<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

class Product extends \Magento\Framework\View\Element\Template
{

    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /** @var \Magento\Catalog\Helper\Image $imageHelper */
    protected $imageHelper;

    /** @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory */
    protected $categoryCollectionFactory;

    /** @var \Magento\Catalog\Model\Product $product */
    protected $product;
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
        $this->product                   = $registry->registry('current_product');
        $this->imageHelper               = $imageHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getCurrentProductInfo()
    {
        /**  */

        $imageWidth  = $this->rejoinerHelper->getImageWidth();
        $imageHeight = $this->rejoinerHelper->getImageHeight();
        $imageUrl = $this->imageHelper->init($this->product, 'category_page_grid')->resize($imageWidth, $imageHeight)->getUrl();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
        $categoriesCollection = $this->categoryCollectionFactory->create();
        $categoriesCollection
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => $this->product->getCategoryIds()])
            ->load();

        $categories = [];
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoriesCollection as $category) {
            $categories[] = $category->getName();
        }

        $productData = [
            'name'        => $this->product->getName(),
            'image_url'   => $imageUrl,
            'price'       => (string) $this->rejoinerHelper->convertPriceToCents($this->product->getPrice()),
            'product_id'  => (string) $this->product->getSku(),
            'product_url' => (string) $this->product->getProductUrl(),
            'category'    => $categories
        ];

        return $productData;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {

        $s =34;

        return [
            'BLOCK_TPL',
            $this->_storeManager->getStore()->getCode(),
            $this->getTemplateFile(),
            'base_url' => $this->getBaseUrl(),
            'template' => $this->getTemplate(),
            'product' => $this->product->getId()
        ];
    }
}