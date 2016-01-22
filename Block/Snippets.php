<?php
namespace Rejoiner\Acr\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Rejoiner\Acr\Helper\Data;
use \Magento\Catalog\Helper\Image;
use \Magento\Customer\Model\Session;
use \Magento\Checkout\Model\Session as CheckoutSession;


class Snippets extends \Magento\Framework\View\Element\Template
{
    private $_checkoutSession;
    private $_rejoinerHelper;
    private $_imageHelper;
    private $_customerSession;

    /**
     * @param Context $context
     * @param Data $rejoinerHelper
     * @param Image $imageHelper
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $rejoinerHelper,
        Image $imageHelper,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_imageHelper     = $imageHelper;
        $this->_rejoinerHelper  = $rejoinerHelper;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getCartItems()
    {
        $items = array();
        $displayPriceWithTax = $this->_rejoinerHelper->getTrackPriceWithTax();

        if ($quote = $this->_checkoutSession->getQuote()) {
            $imageWidth  = $this->_rejoinerHelper->getImageWidth();
            $imageHeight = $this->_rejoinerHelper->getImageHeight();
            foreach ($quote->getAllVisibleItems() as $item) {
                $product  = $item->getProduct();
                $imageUrl = $this->_imageHelper->init($product, 'category_page_grid')->resize($imageWidth, $imageHeight)->getUrl();

                if ($displayPriceWithTax) {
                    $productPrice = $item->getPriceInclTax();
                    $rowTotal     = $item->getRowTotalInclTax();
                } else {
                    $productPrice = $item->getPrice();
                    $rowTotal     = $item->getRowTotal();
                }

                $newItem = array(
                    'name'       => $item->getName(),
                    'image_url'  => $imageUrl,
                    'price'      => (string) $this->_rejoinerHelper->convertPriceToCents($productPrice),
                    'product_id' => (string) $item->getSku(),
                    'item_qty'   => (string) $item->getQty(),
                    'qty_price'  => (string) $this->_rejoinerHelper->convertPriceToCents($rowTotal)
                );
                $items[] = $newItem;
            }
        }
        return $items;
    }

    /**
     * @return string
     */
    public function getCartData()
    {
        $result = '';
        $displayPriceWithTax = $this->_rejoinerHelper->getTrackPriceWithTax();
        if ($quote = $this->_checkoutSession->getQuote()) {
            $total = $displayPriceWithTax? $quote->getGrandTotal() : $quote->getSubtotal();
            $result = array(
                'totalItems'   => (string) $quote->getItemsQty(),
                'value'        => (string) $this->_rejoinerHelper->convertPriceToCents($total),
                'returnUrl'    => (string) $this->_rejoinerHelper->getRestoreUrl()
            );
            if ($this->_rejoinerHelper->getIsEnabledCouponCodeGeneration()) {
                $result['promo'] = $this->_rejoinerHelper->generateCouponCode();
            }

            if ($this->isCustomerLoggedIn()) {
                $result['email'] = $this->getCustomer()->getEmail();
            }

        }
        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return \Rejoiner\Acr\Helper\Data
     */
    public function getRejoinerHelper()
    {
        return $this->_rejoinerHelper;
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomerData();
    }
}