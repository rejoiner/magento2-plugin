<?php
/**
* Copyright © 2016 Rejoiner. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Rejoiner\Acr\Helper;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\App\Helper\Context;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon\Codegenerator;
use Magento\SalesRule\Model\CouponFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Rejoiner\Acr\Logger\Logger;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\SalesRule\Helper\Coupon;
use Magento\Catalog\Model\Product;

class Data extends AbstractHelper
{
    const XML_PATH_REJOINER_SITE_ID              = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_DOMAIN               = 'checkout/rejoiner_acr/domain';
    const XML_PATH_REJOINER_TRACK_NUMBERS        = 'checkout/rejoiner_acr/track_numbers';
    const XML_PATH_REJOINER_TRACK_PRICE_WITH_TAX = 'checkout/rejoiner_acr/track_price_with_tax';
    const XML_PATH_REJOINER_PERSIST_FORMS        = 'checkout/rejoiner_acr/persist_forms';
    const XML_PATH_REJOINER_DEBUGGER_ENABLED     = 'checkout/rejoiner_acr/debug_enabled';
    const XML_PATH_REJOINER_API_KEY              = 'checkout/rejoiner_acr/api_key';
    const XML_PATH_REJOINER_API_SECRET           = 'checkout/rejoiner_acr/api_secret';
    const XML_PATH_REJOINER_API_SITE_ID          = 'checkout/rejoiner_acr/site_id';
    const XML_PATH_REJOINER_PROCESS_BY_CRON      = 'checkout/rejoiner_acr/process_by_cron';
    const XML_PATH_REJOINER_COUPON_GENERATION    = 'checkout/rejoiner_acr/coupon_code';
    const XML_PATH_REJOINER_COUPON_RULE          = 'checkout/rejoiner_acr/coupon_rule';
    const XML_PATH_REJOINER_THUMBNAIL_WIDTH      = 'checkout/rejoiner_acr/thumbnail_size_width';
    const XML_PATH_REJOINER_THUMBNAIL_HEIGHT     = 'checkout/rejoiner_acr/thumbnail_size_height';
    const REJOINER_API_URL                    = 'https://app.rejoiner.com';
    const REJOINER_API_REQUEST_PATH           = '/api/1.0/site/%s/lead/convert';
    const REMOVED_CART_ITEM_SKU_VARIABLE      = 'rejoiner_sku';

    /**
     * @var $_currentProtocolSecurity null|bool
     */
    protected $_currentProtocolSecurity = null;

    /**
     * @var $checkoutSession Session
     */
    protected $checkoutSession;

    /**
     * @var $_urlInterface \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var $scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var $codegenerator Codegenerator
     */
    protected $codegenerator;

    /**
     * @var $couponFactory CouponFactory
     */
    protected $couponFactory;

    /**
     * @var $httpClient \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClient;

    /**
     * @var $logger Logger
     */
    protected $logger;

    /**
     * Data constructor.
     * @param Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlInterface
     * @param ZendClientFactory $httpClient
     * @param RuleRepositoryInterface $ruleRepository
     * @param Codegenerator $codegenerator
     * @param CouponFactory $couponFactory
     * @param Logger $logger
     * @param Context $context
     */
    public function __construct(
        Session $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlInterface,
        ZendClientFactory $httpClient,
        RuleRepositoryInterface $ruleRepository,
        Codegenerator $codegenerator,
        CouponFactory $couponFactory,
        Logger $logger,
        Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_urlInterface    = $urlInterface;
        $this->scopeConfig     = $scopeConfig;
        $this->ruleRepository   = $ruleRepository;
        $this->codegenerator    = $codegenerator;
        $this->couponFactory    = $couponFactory;
        $this->logger           = $logger;
        $this->httpClient       = $httpClient;
        parent::__construct($context);
    }

    /**
     * @param $price
     * @return float
     */
    public function convertPriceToCents($price) {
        return round($price * 100);
    }

    /**
     * Generates restore url.
     * @return string
     */
    public function getRestoreUrl()
    {
        $product = [];
        try {
            foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
                $options = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                $options['qty'] = $item->getQty();
                $options['product'] = $item->getProductId();
                $product[] = $options;
            }
        } catch (\Exception $e) {
            $this->log(_('There is some problem with serialized data'));
        }
        $googleAttributesArray = $this->getGoogleAttributes();
        $customAttributesArray = $this->getCustomAttributes();
        $url = $this->_urlInterface->getUrl('rejoiner/addtocart?'.http_build_query(array_merge($product, $googleAttributesArray, $customAttributesArray)));
        return substr($url, 0, strlen($url)-1);
    }

    /**
     * Returns additional google attributes set in Admin Panel
     *
     * @return array
     */
    protected function getGoogleAttributes() {
        $result = [];
        if ($googleAnalitics = $this->scopeConfig->getValue('checkout/rejoiner_acr/google_attributes', ScopeInterface::SCOPE_STORE)) {
            foreach (unserialize($googleAnalitics) as $attr) {
                if (isset($attr['attr_name']) && isset($attr['value'])) {
                    $result[$attr['attr_name']] = $attr['value'];
                }
            }
        }
        return $result;
    }

    /**
     * Returns custom additional attributes set in Admin Panel
     *
     * @return array
     */
    protected function getCustomAttributes() {
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
     * Generate discount coupon code according to selected shopping cart rule.
     *
     * @return string
     */
    public function generateCouponCode()
    {
        $couponCode = $this->checkoutSession->getQuote()->getPromo();
        $ruleId = $this->scopeConfig->getValue(self::XML_PATH_REJOINER_COUPON_RULE);
        $ruleItem = $this->ruleRepository->getById($ruleId);
        if ($ruleItem->getUseAutoGeneration() && !$couponCode)
        {
            $couponCode = $this->codegenerator->generateCode();
            $salesRuleModel = $this->couponFactory->create();
            $salesRuleModel->setRuleId($ruleId)
                ->setCode($couponCode)
                ->setUsageLimit(1)
                ->setCreatedAt(time())
                ->setType(Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
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
        return $this->scopeConfig->getValue(self::XML_PATH_REJOINER_THUMBNAIL_WIDTH);
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
        if ($this->scopeConfig->getValue(self::XML_PATH_REJOINER_DEBUGGER_ENABLED)) {
            $this->logger->info($message);
        }
    }

    /**
     * @return bool|mixed
     */
    public function checkRemovedItem()
    {
        $session = $this->checkoutSession;
        if ($session->hasData(self::REMOVED_CART_ITEM_SKU_VARIABLE)) {
            $removedItems = $session->getData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            $session->unsetData(self::REMOVED_CART_ITEM_SKU_VARIABLE);
            return $removedItems;
        }
        return false;
    }

    /**
     * @param $orderModel
     * @return int
     * @throws \Zend_Http_Client_Exception
     */
    public function sendInfoToRejoiner(OrderInterface $orderModel)
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
        $client = $this->httpClient->create(['uri' => self::REJOINER_API_URL . $requestPath]);
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
                $this->log(__('%1 : Everything is alright.', $responseCode));
                break;
            case '400':
                $this->log(__('%1  : required params were not specified and/or the body was malformed', $responseCode));
                break;
            case '403':
                $this->log(__('%1: failed authentication and/or incorrect signature', $responseCode));
                break;
            case '500':
                $this->log(__('%1 : internal error, contact us for details', $responseCode));
                break;
            default:
                $this->log(__('%1 : unexpected response code', $responseCode));
                break;
        }
        return $responseCode;
    }

    /**
     * @param $product
     * @param $categoriesArray
     * @return string
     */
    public function getProductCategories(Product $product, $categoriesArray)
    {
        $result = array();
        foreach ($product->getCategoryIds() as $catId) {
            if (isset($categoriesArray[$catId])) {
                $result[] = $categoriesArray[$catId]->getName();
            }
        }
        return implode(' ', $result);
    }
}