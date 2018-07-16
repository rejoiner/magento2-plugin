<?php
/**
 * Copyright © 2018 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Helper;

class Snippets extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /** @var \Magento\Catalog\Helper\Image $imageHelper */
    protected $imageHelper;

    /** @var \Magento\Checkout\Model\Session $checkoutSession */
    protected $checkoutSession;

    /** @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory */
    protected $categoryCollectionFactory;

    /** @var \Magento\Framework\Locale\Resolver $localeResolver */
    protected $localeResolver;

    /** @var \Magento\Framework\Registry $registry */
    protected $registry;

    /** @var array $quoteItemsData */
    protected static $quoteItemsData;

    /**
     * @var ItemsData
     */
    private $itemsData;

    /**
     * Base constructor.
     * @param Data $rejoinerHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Registry $registry
     * @param ItemsData $itemsData
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Registry $registry,
        ItemsData $itemsData,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->rejoinerHelper            = $rejoinerHelper;
        $this->imageHelper               = $imageHelper;
        $this->checkoutSession           = $checkoutSession;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->localeResolver            = $localeResolver;
        $this->registry                  = $registry;
        $this->itemsData = $itemsData;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getCartItems()
    {
        return $this->itemsData->getCartItems($this->getQuote());
    }

    /**
     * @return array
     */
    public function getCartData()
    {
        $result = [];
        $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
        $quote = $this->getQuote();

        if ($quote->getAllVisibleItems()) {
            $total = $displayPriceWithTax? $quote->getGrandTotal() : $quote->getSubtotal();
            $result = [
                'total_items_count' => (string) intval($quote->getItemsQty()),
                'cart_value'        => (string) $this->rejoinerHelper->convertPriceToCents($total),
                'return_url'        => (string) $this->rejoinerHelper->getRestoreUrl()
            ];
            if ($this->rejoinerHelper->getIsEnabledCouponCodeGeneration()) {
                $result['promo'] = $this->rejoinerHelper->generateCouponCode();
            }
        }

        return $result;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
}