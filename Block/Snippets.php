<?php
namespace Rejoiner\Acr\Block;

class Snippets extends \Magento\Framework\View\Element\Template
{
    private $_checkoutSession;
    private $_rejoinerHelper;
    private $_imageHelper;
    private $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_imageHelper     = $imageHelper;
        $this->_rejoinerHelper  = $rejoinerHelper;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }


    public function getCartItems()
    {
        $items = array();
        if ($quote = $this->_checkoutSession->getQuote()) {
            $imageWidth  = $this->_rejoinerHelper->getImageWidth();
            $imageHeight = $this->_rejoinerHelper->getImageHeight();
            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $imageUrl = $this->_imageHelper->init($product, 'category_page_grid')->resize($imageWidth, $imageHeight)->getUrl();
                $newItem = array(
                    'name'       => $item->getName(),
                    'image_url'  => $imageUrl,
                    'price'      => (string) $this->_rejoinerHelper->convertPriceToCents($item->getPriceInclTax()),
                    'product_id' => (string) $item->getSku(),
                    'item_qty'   => (string) $item->getQty(),
                    'qty_price'  => (string) $this->_rejoinerHelper->convertPriceToCents($item->getRowTotalInclTax())
                );
                $items[] = $newItem;
            }
        }
        return $items;
    }

    public function getCartData()
    {
        $result = '';
        if ($quote = $this->_checkoutSession->getQuote()) {
            $result = array(
                'totalItems'   => (string) $quote->getItemsQty(),
                'value'        => (string) $this->_rejoinerHelper->convertPriceToCents($quote->getGrandTotal()),
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

    public function getRejoinerHelper()
    {
        return $this->_rejoinerHelper;
    }

    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getCustomer()
    {
        return $this->_customerSession->getCustomerData();
    }


}