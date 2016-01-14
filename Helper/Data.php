<?php
namespace Rejoiner\Acr\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_REJOINER_SITE_ID           = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_DOMAIN            = 'checkout/rejoiner_acr/domain';
    const XML_PATH_REJOINER_TRACK_NUMBERS     = 'checkout/rejoiner_acr/track_numbers';
    const XML_PATH_REJOINER_PERSIST_FORMS     = 'checkout/rejoiner_acr/persist_forms';
    const XML_PATH_REJOINER_DEBUGGER          = 'checkout/rejoiner_acr/debug_enabled';
    const XML_PATH_REJOINER_API_KEY           = 'checkout/rejoiner_acr/api_key';
    const XML_PATH_REJOINER_API_SECRET        = 'checkout/rejoiner_acr/api_secret';
    const XML_PATH_REJOINER_API_SITE_ID       = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_PROCESS_BY_CRON   = 'checkout/rejoiner_acr/process_by_cron';
    const XML_PATH_REJOINER_COUPON_GENERATION = 'checkout/rejoiner_acr/coupon_code';
    const XML_PATH_REJOINER_COUPON_RULE       = 'checkout/rejoiner_acr/salesrule_model';
    const XML_PATH_REJOINER_THUMBNAIL_WIDTH   = 'checkout/rejoiner_acr/thumbnail_size_width';
    const XML_PATH_REJOINER_THUMBNAIL_HEIGHT  = 'checkout/rejoiner_acr/thumbnail_size_height';


    const REJOINER_API_URL                    = 'https://app.rejoiner.com';
    const REJOINER_API_REQUEST_PATH           = '/api/1.0/site/%s/lead/convert';
    const REMOVED_CART_ITEM_SKU_VARIABLE      = 'rejoiner_sku';
    const REJOINER_API_LOG_FILE               = 'rejoiner_api.log';


    protected $_currentProtocolSecurity = null;
    protected $_checkoutSession;
    protected $_urlInterface;
    protected $_scopeConfig;
    protected $_objectInterface;
    protected $_sessionManager;
    protected $_httpClient;


    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
         \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\HTTP\ZendClientFactory $httpClient,
        \Magento\Framework\ObjectManagerInterface $objectInterface

    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_urlInterface    = $urlInterface;
        $this->_scopeConfig     = $scopeConfig;
        $this->_objectInterface = $objectInterface;
        $this->_sessionManager  = $sessionManager;
        $this->_httpClient      = $httpClient;
    }

    public function convertPriceToCents($price) {
        return round($price*100);
    }

    public function getRestoreUrl()
    {
        $product = array();
        if ($items = $this->_checkoutSession->getQuote()->getAllVisibleItems()) {
            foreach ($items as $item) {
                $options = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                $options['qty'] = $item->getQty();
                $options['product'] = $item->getProductId();
                $product[] = $options;
            }
        }
        $googleAttributesArray = $this->returnGoogleAttributes();
        $customAttributesArray = $this->returnCustomAttributes();
        $url = $this->_urlInterface->getUrl('rejoiner/addtocart?'.http_build_query(array_merge($product, $googleAttributesArray, $customAttributesArray)));
        return substr($url, 0, strlen($url)-1);
    }


    public function returnGoogleAttributes() {
        $result = array();
        if ($googleAnalitics = $this->_scopeConfig->getValue('checkout/rejoiner_acr/google_attributes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            foreach (unserialize($googleAnalitics) as $attr) {
                if ($attr['attr_name'] && $attr['value']) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }

    public function returnCustomAttributes() {
        $result = array();
        if ($customAttr = $this->_scopeConfig->getValue('checkout/rejoiner_acr/custom_attributes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            foreach (unserialize($customAttr) as $attr) {
                if ($attr['attr_name'] && $attr['value']) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }


    public function checkHttps()
    {
        if (empty($this->_currentProtocolSecurity)) {
            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $secure = true;
            }
            else {
                $secure = false;
            }
            $this->_currentProtocolSecurity = $secure;
        } else {
            $secure = $this->_currentProtocolSecurity;
        }

        return $secure;
    }


    public function generateCouponCode()
    {
        $couponCode = $this->_checkoutSession->getQuote()->getPromo();
        $rule_id = $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_RULE);
        $ruleItem = $this->_objectInterface->get('\Magento\SalesRule\Model\Rule')->load($rule_id);
        if ($ruleItem->getUseAutoGeneration() && !$couponCode)
        {
            $couponCode = $this->_objectInterface->get('\Magento\SalesRule\Model\Coupon\Codegenerator')->generateCode();
            $salesRuleModel = $this->_objectInterface->get('Magento\SalesRule\Model\Coupon');
            $salesRuleModel->setRuleId($rule_id)
                ->setCode($couponCode)
                ->setUsageLimit(1)
                ->setCreatedAt(time())
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->save();
            $this->_checkoutSession->getQuote()->setPromo($couponCode)->save();
        }
        return $couponCode;
    }


    public function getDomain()
    {
        $domain = trim($this->_scopeConfig->getValue(self::XML_PATH_REJOINER_DOMAIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($domain[0] == '.') {
            return $domain;
        } else {
            return '.' . $domain;
        }
    }

    public function getRejoinerSiteId()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_SITE_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getTrackNumberEnabled()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_TRACK_NUMBERS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPersistFormsEnabled()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_PERSIST_FORMS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIsEnabledCouponCodeGeneration()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_GENERATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getImageWidth()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_THUMBNAIL_WIDTH);
    }

    public function getImageHeight()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_THUMBNAIL_HEIGHT);
    }

    public function getShouldBeProcessedByCron()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_PROCESS_BY_CRON);
    }

    public function log($message)
    {
        if ($this->_scopeConfig->getValue(self::XML_PATH_REJOINER_DEBUGGER)) {
            $writer = $this->_objectInterface->create('\Zend\Log\Writer\Stream', array('streamOrUrl' => BP . '/var/log/' . self::REJOINER_API_LOG_FILE));
            $logger = $this->_objectInterface->get('\Zend\Log\Logger');
            $logger->addWriter($writer);
            $logger->info($message);
        }
    }

    public function checkRemovedItem()
    {
        $session = $this->_sessionManager;
        if ($session->hasData(self::REMOVED_CART_ITEM_SKU_VARIABLE)) {
            $removedItems = $session->getData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            $session->unsetData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            return $removedItems;
        }
        return false;
    }


    public function sendInfoToRejoiner($orderModel)
    {
        $apiKey = $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_API_KEY);
        $apiSecret = utf8_encode($this->_scopeConfig->getValue(self::XML_PATH_REJOINER_API_SECRET));
        $siteId = $this->_scopeConfig->getValue(self::XML_PATH_REJOINER_API_SITE_ID);
        $requestPath = sprintf(self::REJOINER_API_REQUEST_PATH, $siteId);
        $customerEmail = $orderModel->getBillingAddress()->getEmail();
        $requestBody = utf8_encode(sprintf('{"email": "%s"}', $customerEmail));
        $hmacData = utf8_encode(implode("\n", array(\Zend_Http_Client::POST, $requestPath, $requestBody)));
        $codedApiSecret = base64_encode(hash_hmac('sha1', $hmacData, $apiSecret, true));
        $authorization = sprintf('Rejoiner %s:%s', $apiKey, $codedApiSecret);
        $client = $this->_httpClient->create(array('uri' => self::REJOINER_API_URL . $requestPath));
        $client->setRawData($requestBody);
        $client->setHeaders(array('Authorization' => $authorization, 'Content-type' => 'application/json;'));
        try {
            $response = $client->request(\Zend_Http_Client::POST);
            $responseCode = $response->getStatus();
        } catch (\Exception $e) {
            $this->log($e->getMessage());
            $responseCode = 000;
        }
        switch ($responseCode) {
            case '200':
                $this->log($responseCode . ': Everything is alright.');
                break;
            case '400':
                $this->log($responseCode . ': required params were not specified and/or the body was malformed');
                break;
            case '403':
                $this->log($responseCode . ': failed authentication and/or incorrect signature');
                break;
            case '500':
                $this->log($responseCode . ': internal error, contact us for details');
                break;
            default:
                $this->log($responseCode . ': unexpected response code');
                break;
        }
        return $responseCode;
    }




}