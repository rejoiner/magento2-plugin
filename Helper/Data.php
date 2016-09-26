<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Sales\Model\Order;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_REJOINER_SITE_ID              = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_DOMAIN               = 'checkout/rejoiner_acr/domain';
    const XML_PATH_REJOINER_TRACK_NUMBERS        = 'checkout/rejoiner_acr/track_numbers';
    const XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX = 'checkout/rejoiner_acr/track_price_with_tax';
    const XML_PATH_REJOINER_PERSIST_FORMS        = 'checkout/rejoiner_acr/persist_forms';
    const XML_PATH_REJOINER_DEBUG_ENABLED        = 'checkout/rejoiner_acr/debug_enabled';
    const XML_PATH_REJOINER_API_KEY              = 'checkout/rejoiner_acr/api_key';
    const XML_PATH_REJOINER_API_SECRET           = 'checkout/rejoiner_acr/api_secret';
    const XML_PATH_REJOINER_API_SITE_ID          = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_PROCESS_BY_CRON      = 'checkout/rejoiner_acr/process_by_cron';
    const XML_PATH_REJOINER_COUPON_GENERATION    = 'checkout/rejoiner_acr/coupon_code';
    const XML_PATH_REJOINER_COUPON_RULE          = 'checkout/rejoiner_acr/salesrule_model';
    const XML_PATH_REJOINER_THUMBNAIL_WIDTH      = 'checkout/rejoiner_acr/thumbnail_size_width';
    const XML_PATH_REJOINER_THUMBNAIL_HEIGHT     = 'checkout/rejoiner_acr/thumbnail_size_height';

    const REJOINER_API_URL                    = 'https://app.rejoiner.com';
    const REJOINER_API_REQUEST_PATH           = '/api/1.0/site/%s/lead/convert';
    const REMOVED_CART_ITEM_SKU_VARIABLE      = 'rejoiner_sku';

    /** @var bool $currentProtocolSecurity */
    protected $currentProtocolSecurity = false;

    /** @var \Magento\Checkout\Model\Session $_checkoutSession */
    protected $checkoutSession;

    /** @var \Magento\Framework\UrlInterface $urlInterface */
    protected $urlInterface;

    /** @var \Magento\Framework\ObjectManagerInterface $objectInterface*/
    protected $objectInterface;

    /** @var \Magento\Framework\Session\SessionManager $sessionManager*/
    protected $sessionManager;

    /** @var \Magento\Framework\HTTP\ZendClientFactory $httpClient */
    protected $httpClient;

    /** @var \Monolog\Logger $logger */
    protected $logger;

    /** @var \Magento\Store\Model\StoreManagerInterface  $storeManager*/
    protected $storeManager;

    /**
     * Data constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClient
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param \Monolog\Logger $logger
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\HTTP\ZendClientFactory $httpClient,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Monolog\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->urlInterface    = $urlInterface;
        $this->objectInterface = $objectInterface;
        $this->sessionManager  = $sessionManager;
        $this->_httpClient      = $httpClient;
        $this->logger           = $logger;
        $this->httpClient      = $httpClient;
        parent::__construct($context);
    }

    /**
     * @param $price int
     * @return float
     */
    public function convertPriceToCents($price) {
        return round($price*100);
    }

    /**
     * @return string
     */
    public function getRestoreUrl()
    {
        $product = [];
        if ($items = $this->checkoutSession->getQuote()->getAllVisibleItems()) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($items as $item) {
                $options = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                $options['qty'] = $item->getQty();
                $options['product'] = $item->getProductId();
                $product[] = $options;
            }
        }
        $googleAttributesArray = $this->returnGoogleAttributes();
        $customAttributesArray = $this->returnCustomAttributes();
        $params = array_merge($product, $googleAttributesArray, $customAttributesArray);
        $url = $this->urlInterface->getUrl('rejoiner/addtocart', [
            '_query'  => $params,
            '_secure' => true
        ]);
        return $url;
    }

    /**
     * @return array
     */
    public function returnGoogleAttributes() {
        $result = [];
        if ($googleAnalitics = $this->scopeConfig->getValue('checkout/rejoiner_acr/google_attributes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            foreach (unserialize($googleAnalitics) as $attr) {
                if ($attr['attr_name'] && $attr['value']) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function returnCustomAttributes()
    {
        $result = [];
        if ($customAttr = $this->scopeConfig->getValue('checkout/rejoiner_acr/custom_attributes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            foreach (unserialize($customAttr) as $attr) {
                if ($attr['attr_name'] && $attr['value']) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function generateCouponCode()
    {
        $couponCode = $this->checkoutSession->getQuote()->getPromo();
        $rule_id = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_RULE);
        $ruleItem = $this->objectInterface->get('\Magento\SalesRule\Model\Rule')->load($rule_id);
        if ($ruleItem->getUseAutoGeneration() && !$couponCode)
        {
            $couponCode = $this->objectInterface->get('\Magento\SalesRule\Model\Coupon\Codegenerator')->generateCode();
            $salesRuleModel = $this->objectInterface->get('Magento\SalesRule\Model\Coupon');
            $salesRuleModel->setRuleId($rule_id)
                ->setCode($couponCode)
                ->setUsageLimit(1)
                ->setCreatedAt(time())
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->save();
            $this->checkoutSession->getQuote()->setPromo($couponCode)->save();
        }
        return $couponCode;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        $domain = trim($this->scopeConfig->getValue(self::XML_PATH_REJOINER_DOMAIN, ScopeInterface::SCOPE_STORE));
        if ($domain[0] == '.') {
            return $domain;
        } else {
            return '.' . $domain;
        }
    }

    /**
     * @return string
     */
    public function getRejoinerSiteId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_SITE_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getTrackNumberEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_TRACK_NUMBERS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getTrackPriceWithTax()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPersistFormsEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_PERSIST_FORMS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getIsEnabledCouponCodeGeneration()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_GENERATION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getImageWidth()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_THUMBNAIL_WIDTH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getImageHeight()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_THUMBNAIL_HEIGHT);
    }

    /**
     * @return string
     */
    public function getShouldBeProcessedByCron()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_PROCESS_BY_CRON);
    }

    /**
     * @return string
     */
    public function getRejoinerApiSecret()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SECRET);
    }

    /**
     * @return string
     */
    public function getRejoinerApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_KEY);
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        if ($this->isDebugEnabled()) {
            $this->logger->info($message);
        }
    }

    /**
     * @return mixed
     */
    protected function isDebugEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_REJOINER_DEBUG_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool|mixed
     */
    public function checkRemovedItem()
    {
        $session = $this->sessionManager;
        if ($session->hasData(self::REMOVED_CART_ITEM_SKU_VARIABLE)) {
            $removedItems = $session->getData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            $session->unsetData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            return $removedItems;
        }
        return false;
    }

    /**
     * @param Order $orderModel
     * @return int
     * @throws \Zend_Http_Client_Exception
     */
    public function sendInfoToRejoiner(Order $orderModel)
    {
        $apiKey = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_KEY);
        $apiSecret = utf8_encode($this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SECRET));
        $siteId = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SITE_ID);
        $requestPath = sprintf(self::REJOINER_API_REQUEST_PATH, $siteId);
        $customerEmail = $orderModel->getBillingAddress()->getEmail();
        $requestBody = utf8_encode(sprintf('{"email": "%s"}', $customerEmail));
        $hmacData = utf8_encode(implode("\n", [\Zend_Http_Client::POST, $requestPath, $requestBody]));
        $codedApiSecret = base64_encode(hash_hmac('sha1', $hmacData, $apiSecret, true));
        $authorization = sprintf('Rejoiner %s:%s', $apiKey, $codedApiSecret);
        $client = $this->_httpClient->create(['uri' => self::REJOINER_API_URL . $requestPath]);
        $client->setRawData($requestBody);
        $client->setHeaders(['Authorization' => $authorization, 'Content-type' => 'application/json;']);
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

    public function getProductCategories($product, $categoriesArray)
    {
        $result = [];
        foreach ($product->getCategoryIds() as $catId) {
            if (isset($categoriesArray[$catId])) {
                $result[] = $categoriesArray[$catId]->getName();
            }
        }
        return $result;
    }

}