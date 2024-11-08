<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Rejoiner\Acr\Helper\Data;

class Product extends Template
{
    /** @var Data $rejoinerHelper */
    protected Data $rejoinerHelper;

    /** @var Image $imageHelper */
    protected Image $imageHelper;

    /** @var CollectionFactory $categoryCollectionFactory */
    protected CollectionFactory $categoryCollectionFactory;

    /** @var \Magento\Catalog\Model\Product $product */
    protected \Magento\Catalog\Model\Product $product;

    /**
     * Base constructor.
     * @param Data $rejoinerHelper
     * @param Image $imageHelper
     * @param CollectionFactory $categoryCollectionFactory
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $rejoinerHelper,
        Image $imageHelper,
        CollectionFactory $categoryCollectionFactory,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->rejoinerHelper            = $rejoinerHelper;
        $this->product                   = $registry->registry('current_product');
        $this->imageHelper               = $imageHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getCurrentProductInfo(): array
    {
        $imageWidth  = $this->rejoinerHelper->getImageWidth();
        $imageHeight = $this->rejoinerHelper->getImageHeight();
        $imageUrl = $this->imageHelper
            ->init($this->product, 'category_page_grid')
            ->resize($imageWidth, $imageHeight)
            ->getUrl();

        $categoriesCollection = $this->categoryCollectionFactory->create();
        $categoriesCollection
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => $this->product->getCategoryIds()])
            ->load();

        $categories = [];

        /** @var Category $category */
        foreach ($categoriesCollection as $category) {
            $categories[] = $category->getName();
        }

        return [
            'name'        => $this->product->getName(),
            'image_url'   => $imageUrl,
            'price'       => (string) $this->rejoinerHelper->convertPriceToCents($this->product->getPrice()),
            'product_id'  => (string) $this->product->getSku(),
            'product_url' => (string) $this->product->getProductUrl(),
            'category'    => $categories
        ];
    }

    /**
     * Get cache key informative items
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo(): array
    {
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
