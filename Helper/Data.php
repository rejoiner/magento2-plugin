<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Helper;

use \Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_REJOINER_ENABLED                         = 'checkout/rejoiner_acr/enabled';
    const XML_PATH_REJOINER_SITE_ID                        = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_DOMAIN                         = 'checkout/rejoiner_acr/domain';
    const XML_PATH_REJOINER_TRACK_NUMBERS                  = 'checkout/rejoiner_acr/track_numbers';
    const XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX           = 'checkout/rejoiner_acr/track_price_with_tax';
    const XML_PATH_REJOINER_PERSIST_FORMS                  = 'checkout/rejoiner_acr/persist_forms';
    const XML_PATH_REJOINER_DEBUG_ENABLED                  = 'checkout/rejoiner_acr/debug_enabled';
    const XML_PATH_REJOINER_API_KEY                        = 'checkout/rejoiner_acr/api_key';
    const XML_PATH_REJOINER_API_SECRET                     = 'checkout/rejoiner_acr/api_secret';
    const XML_PATH_REJOINER_API_SITE_ID                    = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_PROCESS_BY_CRON                = 'checkout/rejoiner_acr/process_by_cron';
    const XML_PATH_REJOINER_COUPON_GENERATION              = 'checkout/rejoiner_acr/coupon_code';
    const XML_PATH_REJOINER_COUPON_RULE                    = 'checkout/rejoiner_acr/salesrule_model';
    const XML_PATH_REJOINER_THUMBNAIL_WIDTH                = 'checkout/rejoiner_acr/thumbnail_size_width';
    const XML_PATH_REJOINER_THUMBNAIL_HEIGHT               = 'checkout/rejoiner_acr/thumbnail_size_height';
    const XML_PATH_REJOINER_PASS_NEW_CUSTOMERS             = 'checkout/rejoiner_acr/passing_new_customers';
    const XML_PATH_REJOINER_LIST_ID                        = 'checkout/rejoiner_acr/list_id';
    const XML_PATH_REJOINER_MARKETING_PERMISSIONS          = 'checkout/rejoiner_acr/marketing_permissions';
    const XML_PATH_REJOINER_MARKETING_LIST_ID              = 'checkout/rejoiner_acr/marketing_list_id';
    const XML_PATH_REJOINER_SUBSCRIBE_GUEST_CHECKOUT       = 'checkout/rejoiner_acr/subscribe_checkout_onepage_index';
    const XML_PATH_REJOINER_SUBSCRIBE_ACCOUNT_REGISTRATION = 'checkout/rejoiner_acr/subscribe_customer_account_create';
    const XML_PATH_REJOINER_SUBSCRIBE_LOGIN_CHECKOUT       = 'checkout/rejoiner_acr/subscribe_customer_account_login';
    const XML_PATH_REJOINER_SUBSCRIBE_CUSTOMER_ACCOUNT     = 'checkout/rejoiner_acr/subscribe_newsletter_manage_index';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_DEFAULT     = 'checkout/rejoiner_acr/subscribe_checkbox_default';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_LABEL       = 'checkout/rejoiner_acr/subscribe_checkbox_label';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_SELECTOR    = 'checkout/rejoiner_acr/subscribe_checkbox_selector';
    const XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_STYLE       = 'checkout/rejoiner_acr/subscribe_checkbox_style';
    const XML_PATH_REJOINER_INTEGRATIONS_AFFIRM            = 'checkout/rejoiner_acr/integrations_affirm';

    const STATUS_SUBSCRIBED                                = 1;
    const STATUS_UNSUBSCRIBED                              = 2;

    const REJOINER_API_URL                      = 'https://app.rejoiner.com';
    const REJOINER_API_REQUEST_PATH             = '/api/1.0/site/%s/lead/convert';
    const REJOINER_API_ADD_TO_LIST_REQUEST_PATH = '/api/1.0/site/%s/contact_add';
    const REJOINER_API_UNSUBSCRIBE_REQUEST_PATH = '/api/1.0/site/%s/lead/unsubscribe';
    const REMOVED_CART_ITEM_SKU_VARIABLE        = 'rejoiner_sku';

    /** @var \Magento\Checkout\Model\Session $_checkoutSession */
    private $checkoutSession;

    /** @var \Magento\Framework\Session\SessionManager $sessionManager*/
    private $sessionManager;

    /** @var \Magento\Framework\HTTP\ZendClientFactory $httpClient */
    private $httpClient;

    /** @var \Monolog\Logger $logger */
    private $logger;

    /** @var \Magento\SalesRule\Model\RuleFactory $ruleFactory */
    private $ruleFactory;

    /** @var \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegeneratorFactory */
    private $codegeneratorFactory;

    /** @var \Magento\SalesRule\Model\CouponFactory $couponFactory */
    private $couponFactory;

    /** @var Serializer */
    private $serializer;

    /**
     * Data constructor.
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegeneratorFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClient
     * @param \Monolog\Logger $logger
     * @param Serializer $serializer
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegeneratorFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Framework\HTTP\ZendClientFactory $httpClient,
        \Monolog\Logger $logger,
        Serializer $serializer,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->couponFactory        = $couponFactory;
        $this->codegeneratorFactory = $codegeneratorFactory;
        $this->ruleFactory          = $ruleFactory;
        $this->checkoutSession      = $checkoutSession;
        $this->sessionManager       = $sessionManager;
        $this->logger               = $logger;
        $this->httpClient           = $httpClient;

        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * @param $price int
     * @return float
     */
    public function convertPriceToCents($price)
    {
        return round($price*100);
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getRestoreUrl()
    {
        $product = [];
        if ($items = $this->checkoutSession->getQuote()->getAllVisibleItems()) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($items as $item) {
                $options = $this->serializer->decode($item->getOptionByCode('info_buyRequest')->getValue());
                $options['qty'] = $item->getQty();
                $options['product'] = $item->getProductId();
                $product[] = $options;
            }
        }
        $googleAttributesArray = $this->returnGoogleAttributes();
        $customAttributesArray = $this->returnCustomAttributes();
        $params = array_merge($product, $googleAttributesArray, $customAttributesArray);
        $url = $this->_urlBuilder->getUrl('rejoiner/addtocart', [
            '_query'  => $params,
            '_secure' => true
        ]);

        return $url;
    }

    /**
     * @return array
     */
    public function returnGoogleAttributes()
    {
        $result = [];
        if ($googleAnalitics = $this->scopeConfig->getValue('checkout/rejoiner_acr/google_attributes', ScopeInterface::SCOPE_STORE)) {
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
        if ($customAttr = $this->scopeConfig->getValue('checkout/rejoiner_acr/custom_attributes', ScopeInterface::SCOPE_STORE)) {
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
        /** @var \Magento\SalesRule\Model\Rule $ruleItem */
        $ruleItem = $this->ruleFactory->create()->load($rule_id);
        if ($ruleItem->getUseAutoGeneration() && !$couponCode) {
            $couponCode = $this->codegeneratorFactory->create()->generateCode();
            /** @var \Magento\SalesRule\Model\Coupon $salesRuleModel */
            $salesRuleModel = $this->couponFactory->create();
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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_ENABLED);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        $domain = trim($this->scopeConfig->getValue(self::XML_PATH_REJOINER_DOMAIN, ScopeInterface::SCOPE_STORE));

        return ($domain[0] == '.') ? $domain : '.' . $domain;
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
    public function getIntegrationsAffirm()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_INTEGRATIONS_AFFIRM, ScopeInterface::SCOPE_STORE);
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
     * @return bool
     */
    public function getRejoinerMarketingPermissions()
    {
        return $this->isEnabled() && $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_MARKETING_PERMISSIONS);
    }

    /**
     * @return string
     */
    public function getRejoinerMarketingListID()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_MARKETING_LIST_ID);
    }

    /**
     * @return bool
     */
    public function getRejoinerSubscribeGuestCheckout()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_SUBSCRIBE_GUEST_CHECKOUT);
    }

    /**
     * @return bool
     */
    public function getRejoinerSubscribeAccountRegistration()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_SUBSCRIBE_ACCOUNT_REGISTRATION);
    }

    /**
     * @return bool
     */
    public function getRejoinerSubscribeLoginCheckout()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_SUBSCRIBE_LOGIN_CHECKOUT);
    }

    /**
     * @return bool
     */
    public function getRejoinerSubscribeCustomerAccount()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_SUBSCRIBE_CUSTOMER_ACCOUNT);
    }

    /**
     * @return bool
     */
    public function getRejoinerSubscribeCheckedDefault()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_DEFAULT);
    }

    /**
     * @return string
     */
    public function getRejoinerSubscribeCheckboxLabel()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_LABEL);
    }

    /**
     * @return string
     */
    public function getRejoinerSubscribeCheckboxSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_SELECTOR);
    }

    /**
     * @return string
     */
    public function getRejoinerSubscribeCheckboxStyle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_SUBSCRIBE_CHECKBOX_STYLE);
    }

    /**
     * @param string $message
     * @param bool $force
     */
    public function log($message, $force = false)
    {
        if ($this->isDebugEnabled() || $force) {
            if ($force) {
                $this->logger->critical($message);
            } else {
                $this->logger->info($message);
            }
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
     * @param \Magento\Sales\Model\Order $orderModel
     */
    public function sendInfoToRejoiner(\Magento\Sales\Model\Order $orderModel)
    {
        try {
            $customerEmail = $orderModel->getBillingAddress()->getEmail();
            $this->convert($customerEmail);

            if ($this->scopeConfig->getValue(self::XML_PATH_REJOINER_PASS_NEW_CUSTOMERS) && $this->scopeConfig->getValue(self::XML_PATH_REJOINER_LIST_ID)) {
                $listId = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_LIST_ID);
                $email = $orderModel->getCustomerEmail();
                $customerName = $orderModel->getBillingAddress()->getFirstname();
                $this->addToList($listId, $email, $customerName);
            }
        } catch (\Exception $e) {}
    }

    /**
     * @param string $email
     * @param string $customerName
     * @return $this
     */
    public function subscribe($email, $customerName = '')
    {
        $this->addToList($this->getRejoinerMarketingListID(), $email, $customerName);

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function unSubscribe($email)
    {
        $client = $this->prepareClient(self::REJOINER_API_UNSUBSCRIBE_REQUEST_PATH, ['email' => $email]);
        $this->sendRequest($client);

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    private function convert($email)
    {
        try {
            $client = $this->prepareClient(self::REJOINER_API_REQUEST_PATH, ['email' => $email]);
            $this->sendRequest($client);
        } catch (\Exception $e) {}

        return $this;
    }

    /**
     * @param $listId
     * @param $email
     * @param string $customerName
     * @return $this
     */
    private function addToList($listId, $email, $customerName = '')
    {
        if (!$listId) {
            return $this;
        }

        $data = [
            'email'      => $email,
            'list_id'    => $listId,
            'first_name' => $customerName
        ];

        $client = $this->prepareClient(self::REJOINER_API_ADD_TO_LIST_REQUEST_PATH, $data);
        $this->sendRequest($client);

        return $this;
    }

    /**
     * @param $path
     * @param array $data
     * @return \Magento\Framework\HTTP\ZendClient
     * @throws \Exception
     */
    private function prepareClient($path, array $data)
    {
        $apiKey         = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_KEY);
        $siteId         = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SITE_ID);
        $apiSecret      = utf8_encode($this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SECRET));

        if (!$apiKey || !$siteId || !$apiSecret || empty($data)) {
            $error = 'Missing API credentials';
            $this->log($error, true);
            throw new \Exception($error);
        }

        $requestBody    = utf8_encode(json_encode($data));
        $requestPath    = sprintf($path, $siteId);
        $hmacData       = utf8_encode(implode("\n", [\Zend_Http_Client::POST, $requestPath, $requestBody]));
        $codedApiSecret = base64_encode(hash_hmac('sha1', $hmacData, $apiSecret, true));
        $authorization  = sprintf('Rejoiner %s:%s', $apiKey , $codedApiSecret);
        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client         = $this->httpClient->create(['uri' => self::REJOINER_API_URL . $requestPath]);
        $client->setRawData($requestBody);
        $client->setHeaders(['Authorization' => $authorization, 'Content-type' => 'application/json;']);

        return $client;
    }

    /**
     * @param \Magento\Framework\HTTP\ZendClient $client
     * @return int
     * @throws \Exception
     */
    private function sendRequest(\Magento\Framework\HTTP\ZendClient $client)
    {
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
                $error = $responseCode . ': required params were not specified and/or the body was malformed';
                $this->log($error, true);
                throw new \Exception($error);
                break;
            case '403':
                $error = $responseCode . ': failed authentication and/or incorrect signature';
                $this->log($error, true);
                throw new \Exception($error);
                break;
            case '500':
                $error = $responseCode . ': internal error, contact us for details';
                $this->log($error, true);
                throw new \Exception($error);
                break;
            default:
                $error = $responseCode . ': unexpected response code';
                $this->log($error, true);
                throw new \Exception($error);
                break;
        }

        return $responseCode;
    }


    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $categoriesArray
     * @return array
     */
    public function getProductCategories(\Magento\Catalog\Model\Product $product, $categoriesArray)
    {
        $result = [];
        foreach ($product->getCategoryIds() as $catId) {
            if (isset($categoriesArray[$catId])) {
                /** @var \Magento\Catalog\Model\Category $category */
                $category = $categoriesArray[$catId];
                $result[] = $category->getName();
            }
        }

        return $result;
    }
}
