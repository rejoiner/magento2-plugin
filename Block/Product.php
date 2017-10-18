<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

class Product extends Base
{
    /**
     * Product constructor.
     * @param \Magento\Framework\Registry $registry
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
        parent::__construct(
            $rejoinerHelper,
            $jsonHelper,
            $imageHelper,
            $checkoutSession,
            $categoryCollectionFactory,
            $localeResolver,
            $registry,
            $context,
            $data
        );
    }

    /**
     * @return string
     */
    public function getCurrentProductInfo()
    {
        /** $product \Magento\Catalog\Model\Product */
        $product = $this->registry->registry('current_product');
        $imageWidth  = $this->rejoinerHelper->getImageWidth();
        $imageHeight = $this->rejoinerHelper->getImageHeight();
        $imageUrl = $this->imageHelper->init($product, 'category_page_grid')->resize($imageWidth, $imageHeight)->getUrl();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoriesCollection */
        $categoriesCollection = $this->categoryCollectionFactory->create();
        $categoriesCollection
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => $product->getCategoryIds()])
            ->load();

        $categories = [];
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoriesCollection as $category) {
            $categories[] = $category->getName();
        }

        $productData = [
            'name'        => $product->getName(),
            'image_url'   => $imageUrl,
            'price'       => (string) $this->rejoinerHelper->convertPriceToCents($product->getPrice()),
            'product_id'  => (string) $product->getSku(),
            'product_url' => (string) $product->getProductUrl(),
            'category'    => $categories
        ];

        return $productData;
    }
}