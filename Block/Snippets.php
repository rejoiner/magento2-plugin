<?php

namespace Rejoiner\Acr\Block;

use Magento\Framework\View\Element\Template\Context;
use Rejoiner\Acr\Helper\Data;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Framework\View\Element\Template;

class Snippets extends Template
{
    /**
     * @var $jsonHelper JsonData
     */
    protected $jsonHelper;

    /**
     * @var $checkoutSession CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var $rejoinerHelper Data
     */
    protected $rejoinerHelper;

    /**
     * @var $imageHelper Image
     */
    protected $imageHelper;

    /**
     * @var $customerSession Session
     */
    protected $customerSession;

    /**
     * @var $items array
     */
    protected $items;

    /**
     * Snippets constructor.
     * @param JsonData $jsonHelper
     * @param Context $context
     * @param Data $rejoinerHelper
     * @param Image $imageHelper
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        JsonData $jsonHelper,
        Context $context,
        Data $rejoinerHelper,
        Image $imageHelper,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper     = $imageHelper;
        $this->rejoinerHelper  = $rejoinerHelper;
        $this->customerSession = $customerSession;
        $this->jsonHelper      = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     * Returns array with information about each product in shopping cart
     *
     * @return array
     */
    public function getCartItems()
    {
        if (!$this->items) {
            $displayPriceWithTax = $this->getRejoinerHelper()->getTrackPriceWithTax();
            $quote = $this->checkoutSession->getQuote();
            $imageWidth = $this->getRejoinerHelper()->getImageWidth();
            $imageHeight = $this->getRejoinerHelper()->getImageHeight();

            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $imageUrl = $this->imageHelper->init($product, 'category_page_grid')->resize($imageWidth, $imageHeight)->getUrl();
                if ($displayPriceWithTax) {
                    $productPrice = $item->getPriceInclTax();
                    $rowTotal = $item->getRowTotalInclTax();
                } else {
                    $productPrice = $item->getPrice();
                    $rowTotal = $item->getRowTotal();
                }
                $newItem = [
                    'name'        => $item->getName(),
                    'image_url'   => $imageUrl,
                    'price'       => (string) $this->getRejoinerHelper()->convertPriceToCents($productPrice),
                    'product_id'  => (string) $item->getSku(),
                    'item_qty'    => (string) $item->getQty(),
                    'product_url' => (string) $product->getProductUrl(),
                    'qty_price'   => (string) $this->getRejoinerHelper()->convertPriceToCents($rowTotal)
                ];
                $this->items[] = $newItem;
            }
        }
        return $this->items;
    }

    /**
     * Returns general shopping cart information
     * @return string
     */
    public function getCartData()
    {
        $displayPriceWithTax = $this->getRejoinerHelper()->getTrackPriceWithTax();
        // No need to check what session returns because Quote object is always returned,
        // but may not contain data if it is empty
        $quote = $this->checkoutSession->getQuote();
        $total = $displayPriceWithTax? $quote->getGrandTotal() : $quote->getSubtotal();
        $result = [
            'totalItems'   => (string) $quote->getItemsQty(),
            'value'        => (string) $this->getRejoinerHelper()->convertPriceToCents($total),
            'returnUrl'    => (string) $this->getRejoinerHelper()->getRestoreUrl()
        ];
        if ($this->getRejoinerHelper()->getIsEnabledCouponCodeGeneration()) {
            $result['promo'] = $this->getRejoinerHelper()->generateCouponCode();
        }

        if ($this->isCustomerLoggedIn()) {
            $result['email'] = $this->getCustomer()->getEmail();
        }

        return $this->getJsonHelper()->jsonEncode($result);
    }

    /**
     * @return \Rejoiner\Acr\Helper\Data
     */
    public function getRejoinerHelper()
    {
        return $this->rejoinerHelper;
    }

    /**
     * @return bool
     */
    protected function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomerData();
    }

    /**
     * @return JsonData
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}

