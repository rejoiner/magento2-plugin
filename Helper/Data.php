<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Helper;

use Magento\Store\Model\ScopeInterface;

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

    const STATUS_SUBSCRIBED                                = 1;
    const STATUS_UNSUBSCRIBED                              = 2;

    const REJOINER2_SITE_ID_LENGTH                         = 7;

    const REJOINER_VERSION_1                               = 'v1';
    const REJOINER_VERSION_2                               = 'v2';

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

    /** @var \Magento\Framework\App\Request\Http $request */
    private $request;

    /**
     * Data constructor.
     *
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegeneratorFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClient
     * @param \Magento\Framework\App\Request\Http $request
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
        \Magento\Framework\App\Request\Http $request,
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
        $this->request              = $request;

        parent::__construct($context);
        $this->serializer = $serializer;
    }

    /**
     * @return string
     */
    public function getRejoinerApiUri()
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return 'https://rj2.rejoiner.com';
            default:
                return 'https://app.rejoiner.com';
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiPath()
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return '/api/v1/%s';
            default:
                return '/api/1.0/site/%s';
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiConvertPath()
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return $this->getRejoinerApiPath() . '/customer/convert/';
            default:
                return $this->getRejoinerApiPath() . '/lead/convert';
        }
    }

    /**
     * @param string $listId
     * @return string
     */
    public function getRejoinerApiAddToListPath($listId)
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return $this->getRejoinerApiPath() . "/lists/$listId/contacts/";
            default:
                return $this->getRejoinerApiPath() . '/contact_add';
        }
    }

    /**
     * @return string
     */
    public function getRejoinerApiUnSubscribePath()
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return $this->getRejoinerApiPath() . '/customer/unsubscribe/';
            default:
                return $this->getRejoinerApiPath() . '/lead/unsubscribe';
        }
    }

    /**
     * @param int $price
     * @return float
     */
    public function convertPriceToCents($price)
    {
        return round($price*100);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        if ($googleAnalitics = $this->scopeConfig->getValue(
            'checkout/rejoiner_acr/google_attributes',
            ScopeInterface::SCOPE_STORE
        )) {
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
        if ($customAttr = $this->scopeConfig->getValue(
            'checkout/rejoiner_acr/custom_attributes',
            ScopeInterface::SCOPE_STORE
        )) {
            foreach (unserialize($customAttr) as $attr) {
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
    public function getExtraCodes()
    {
        $result = [];
        if ($extraCodes = $this->scopeConfig->getValue(
            'checkout/rejoiner_acr/extra_codes',
            ScopeInterface::SCOPE_STORE
        )) {
            foreach (unserialize($extraCodes) as $extraCode) {
                if ($extraCode['promo_param'] && $extraCode['promo_salesrule']) {
                    $result[$extraCode['promo_param']] = $extraCode['promo_salesrule'];
                }
            }
        }

        return $result;
    }

    /**
     * @param $rule_id
     * @param string $param
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateCouponCode($rule_id, $param = 'promo')
    {
        $quote = $this->checkoutSession->getQuote();
        $quotePromo = $quote->getPromo();
        $couponCode = '';

        if ($quotePromo) {
            $codes = unserialize($quotePromo);
            $couponCode = isset($codes[$param]) ? $codes[$param] : '';

            if ($couponCode) {
                return $couponCode;
            }
        }

        /** @var \Magento\SalesRule\Model\Rule $ruleItem */
        $ruleItem = $this->ruleFactory->create()->load($rule_id);
        if ($ruleItem->getUseAutoGeneration()) {
            $couponCode = $this->codegeneratorFactory->create()->generateCode();

            /** @var \Magento\SalesRule\Model\Coupon $salesRuleModel */
            $salesRuleModel = $this->couponFactory->create();
            $salesRuleModel->setRuleId($rule_id)
                ->setCode($couponCode)
                ->setUsageLimit(1)
                ->setCreatedAt(time())
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->save();

            $codes[$param] = $couponCode;

            $quote->setPromo(serialize($codes))->save();
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

        return ($domain[0] === '.') ? $domain : '.' . $domain;
    }

    /**
     * @return string
     */
    public function getRejoinerSiteId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_SITE_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getRejoinerVersion()
    {
        $siteId = $this->getRejoinerSiteId();
        $siteIdLength = strlen($siteId);

        if ($siteIdLength === self::REJOINER2_SITE_ID_LENGTH) {
            return self::REJOINER_VERSION_2;
        }

        return self::REJOINER_VERSION_1;
    }

    /**
     * @return string
     */
    public function getRejoinerScriptUri()
    {
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return 'https://cdn.rejoiner.com/js/v4/rj2.lib.js';
            default:
                return 'https://cdn.rejoiner.com/js/v4/rejoiner.lib.js';
        }
    }

    /**
     * @return string
     */
    public function getTrackNumberEnabled()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_REJOINER_TRACK_NUMBERS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getTrackPriceWithTax()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getPersistFormsEnabled()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_REJOINER_PERSIST_FORMS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getIsEnabledCouponCodeGeneration()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_GENERATION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getCouponCodeRuleId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_RULE, ScopeInterface::SCOPE_STORE);
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
        switch ($this->getRejoinerVersion()) {
            case self::REJOINER_VERSION_2:
                return true;
            default:
                return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SECRET);
        }
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
     * If shopping cart information should be sent to Rejoiner service on current page
     *
     * @return int
     */
    public function getShoppingCartDataOnThisPage()
    {
        return (int) in_array(
            $this->getCurrentPageName(),
            [
                'checkout_cart_index',
                'checkout_index_index',
                'multishipping_checkout_login',
                'multishipping_checkout_addresses'
            ]
        );
    }

    /**
     * @return string
     */
    public function getCurrentPageName()
    {
        return $this->request->getFullActionName();
    }

    /**
     * @return bool|mixed
     */
    public function checkRemovedItem()
    {
        $session = $this->sessionManager;
        $removedItems = $session->getData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
        $session->unsetData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
        return $removedItems;
    }

    /**
     * @param \Magento\Sales\Model\Order $orderModel
     */
    public function sendInfoToRejoiner(\Magento\Sales\Model\Order $orderModel)
    {
        try {
            $customerEmail = $orderModel->getBillingAddress()->getEmail();
            $this->convert($customerEmail);

            $passNewCustomers = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_PASS_NEW_CUSTOMERS);
            $listId = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_LIST_ID);

            if ($passNewCustomers && $listId) {
                $email = $orderModel->getCustomerEmail();
                $customerName = $orderModel->getBillingAddress()->getFirstname();
                $customerLastName = $orderModel->getBillingAddress()->getLastname();
                $this->addToList($listId, $email, $customerName, $customerLastName);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Subscribe customer
     *
     * @param string $email
     * @param string|null $customerName
     * @param string|null $customerLastName
     * @return $this
     * @throws \Exception
     */
    public function subscribe($email, $customerName = null, $customerLastName = null)
    {
        $this->addToList($this->getRejoinerMarketingListID(), $email, $customerName, $customerLastName);

        return $this;
    }

    /**
     * @param string $email
     * @return $this
     * @throws \Exception
     */
    public function unSubscribe($email)
    {
        $apiUnSubscribePath = $this->getRejoinerApiUnSubscribePath();
        $client = $this->prepareClient($apiUnSubscribePath, ['email' => $email]);
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
            $apiConvertPath = $this->getRejoinerApiConvertPath();
            $client = $this->prepareClient($apiConvertPath, ['email' => $email]);
            $this->sendRequest($client);
        } catch (\Exception $e) {
        }

        return $this;
    }

    /**
     * Add customer to the list
     *
     * @param string $listId
     * @param string $email
     * @param string|null $customerName
     * @param string|null $customerLastName
     * @return $this
     * @throws \Exception
     */
    private function addToList($listId, $email, $customerName = null, $customerLastName = null)
    {
        if (!$listId) {
            return $this;
        }

        $data = [
            'email'      => $email,
            'list_id'    => $listId
        ];

        if ($customerName) {
            $data['first_name'] = $customerName;
        }

        if ($customerLastName) {
            $data['last_name'] = $customerLastName;
        }

        $apiAddToListPath = $this->getRejoinerApiAddToListPath($listId);
        $client = $this->prepareClient($apiAddToListPath, $data);
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
        $apiKey          = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_KEY);
        $siteId          = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SITE_ID);
        $rejoinerVersion = $this->getRejoinerVersion();

        if (!$apiKey || !$siteId || empty($data)) {
            $error = 'Missing API credentials';
            $this->log($error, true);
            throw new \Exception($error);
        }

        $requestBody   = utf8_encode(json_encode($data));
        $requestPath   = sprintf($path, $siteId);
        $authorization = sprintf('Rejoiner %s', $apiKey);

        if ($rejoinerVersion === self::REJOINER_VERSION_1) {
            $apiSecret = utf8_encode($this->scopeConfig->getValue(self::XML_PATH_REJOINER_API_SECRET));

            if (!$apiSecret) {
                $error = 'Missing API secret';
                $this->log($error, true);
                throw new \Exception($error);
            }

            $hmacData       = utf8_encode(implode("\n", [\Zend_Http_Client::POST, $requestPath, $requestBody]));
            $codedApiSecret = base64_encode(hash_hmac('sha1', $hmacData, $apiSecret, true));
            $authorization  = sprintf('Rejoiner %s:%s', $apiKey, $codedApiSecret);
        }

        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $rejoinerApiUri = $this->getRejoinerApiUri();
        $client = $this->httpClient->create(['uri' => $rejoinerApiUri . $requestPath]);
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
            case '201':
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
