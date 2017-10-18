<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

use Rejoiner\Acr\Plugin\Framework\Model\Layout\Checkout\DepersonalizePlugin;

class Base extends \Magento\Framework\View\Element\Template
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /** @var \Magento\Framework\Json\Helper\Data $jsonHelper */
    protected $jsonHelper;

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
     * Base constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rejoinerHelper            = $rejoinerHelper;
        $this->jsonHelper                = $jsonHelper;
        $this->imageHelper               = $imageHelper;
        $this->checkoutSession           = $checkoutSession;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->localeResolver            = $localeResolver;
        $this->registry                  = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getCartItems()
    {
        if (!isset(self::$quoteItemsData)) {
            self::$quoteItemsData = [];
            $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
            if ($quote = $this->getQuote()) {
                $categories = [];
                /** @var \Magento\Quote\Model\Quote $quote */
                /** @var \Magento\Quote\Model\Quote\Item $item */
                foreach ($quote->getAllItems() as $item) {
                    $categories = array_merge($categories, $item->getProduct()->getCategoryIds());
                }
                /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
                $categoryCollection = $this->categoryCollectionFactory->create();

                $categoriesArray = $categoryCollection
                    ->addAttributeToSelect('name')
                    ->addFieldToFilter('entity_id', ['in' => $categories])
                    ->load()
                    ->getItems();

                $imageWidth  = $this->rejoinerHelper->getImageWidth();
                $imageHeight = $this->rejoinerHelper->getImageHeight();
                foreach ($quote->getAllVisibleItems() as $item) {
                    $product           = $item->getProduct();
                    $productCategories = $this->rejoinerHelper->getProductCategories($product, $categoriesArray);
                    $imageUrl          = $this->imageHelper->init($product, 'category_page_grid')->resize($imageWidth, $imageHeight)->getUrl();

                    if ($displayPriceWithTax) {
                        $productPrice = $item->getPriceInclTax();
                        $rowTotal     = $item->getRowTotalInclTax();
                    } else {
                        $productPrice = $item->getPrice();
                        $rowTotal     = $item->getRowTotal();
                    }

                    $newItem = [
                        'name'       => $item->getName(),
                        'image_url'  => $imageUrl,
                        'price'      => (string) $this->rejoinerHelper->convertPriceToCents($productPrice),
                        'product_id' => (string) $item->getSku(),
                        'item_qty'   => (string) $item->getQty(),
                        'qty_price'  => (string) $this->rejoinerHelper->convertPriceToCents($rowTotal),
                        'product_url' => (string) $product->getProductUrl(),
                        'category'    => $productCategories
                    ];
                    self::$quoteItemsData[] = $newItem;
                }
            }
        }
        return self::$quoteItemsData;
    }

    /**
     * @return \Rejoiner\Acr\Helper\Data
     */
    public function getRejoinerHelper()
    {
        return $this->rejoinerHelper;
    }

    /**
     * @param array $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->jsonHelper->jsonEncode($data);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        return $this->registry->registry(DepersonalizePlugin::REGISTRY_KEY);
    }
}