<?php
namespace Rejoiner\Acr\Helper;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable as ConfigurableRenderer;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;

class ItemsData extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data $rejoinerHelper
     */
    private $rejoinerHelper;

    /**
     * @var \Magento\Catalog\Helper\Image $imageHelper
     */
    private $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Conversion constructor.
     * @param Data $rejoinerHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        Data $rejoinerHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->rejoinerHelper = $rejoinerHelper;
        $this->imageHelper = $imageHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param Quote|Order $quoteOrOrder
     * @return array
     */
    public function getCartItems($quoteOrOrder)
    {
        $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
        $itemsData = [];
        $categories = [];

        if (!$quoteOrOrder->getAllVisibleItems()) {
            return $itemsData;
        }

        /** @var Quote|Order $quoteOrOrder */
        /** @var QuoteItem|OrderItem $item */
        foreach ($quoteOrOrder->getAllItems() as $item) {
            $categories = array_merge($categories, $item->getProduct()->getCategoryIds());
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();

        $categoriesArray = $categoryCollection
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => array_unique($categories)])
            ->load()
            ->getItems();

        $imageWidth  = $this->rejoinerHelper->getImageWidth();
        $imageHeight = $this->rejoinerHelper->getImageHeight();

        foreach ($quoteOrOrder->getAllVisibleItems() as $item) {
            $product           = $item->getProduct();
            $productCategories = $this->rejoinerHelper->getProductCategories($product, $categoriesArray);
            $imageUrl          = $this->imageHelper
                    ->init(
                        $this->getProductForThumbnail($item, $quoteOrOrder instanceof Order ? $quoteOrOrder : null),
                        'category_page_grid'
                    )
                    ->resize($imageWidth, $imageHeight)->getUrl();

            if ($displayPriceWithTax) {
                $productPrice = $item->getPriceInclTax();
                $rowTotal     = $item->getRowTotalInclTax();
            } else {
                $productPrice = $item->getPrice();
                $rowTotal     = $item->getRowTotal();
            }
            $qty = $item instanceof OrderItem ? $item->getQtyOrdered() : $item->getQty();
            $itemsData[] = [
                'name'        => $item->getName(),
                'image_url'   => $imageUrl,
                'price'       => (int) $this->rejoinerHelper->convertPriceToCents($productPrice),
                'product_id'  => (string) $item->getSku(),
                'item_qty'    => (int) $qty,
                'qty_price'   => (int) $this->rejoinerHelper->convertPriceToCents($rowTotal),
                'product_url' => (string) $product->getProductUrl(),
                'category'    => $productCategories
            ];
        }

        return $itemsData;
    }

    /**
     * @param OrderItem $item
     * @param null|Order $order
     * @return Product
     */
    private function getProductForThumbnail($item, $order = null)
    {
        $product = $item->getProduct();

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $childProduct = $this->getChildProduct($item, $order);

            if ($this->useParentThumbnail()
                || !($childProduct->getThumbnail() && $childProduct->getThumbnail() != 'no_selection')
            ) {
                return $product;
            }

            return $childProduct;
        }

        return $product;
    }

    /**
     * @param QuoteItem|OrderItem $item
     * @param null|Order $order
     * @return Product
     */
    private function getChildProduct($item, $order = null)
    {
        // it is faster to iterate over ordered items then to do some magic or load simple products accidentally on our own
        if ($order) {
            $parentItemId = $item->getId();
            /** @var OrderItem $orderItem */
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getParentItemId() == $parentItemId) {
                    $product = $orderItem->getProduct();
                    break;
                }
            }
            // actually, this should not ever happen because configurable products is always present in cart with its' simple child
            if (!isset($product)) {
                $product = $item->getProduct();
            }
            /** @var \Magento\Quote\Model\Quote\Item\Option $option */
        } elseif ($option = $item->getOptionByCode('simple_product')) {
            $product = $option->getProduct();
        } else {
            $product = $item->getProduct();
        }

        return $product;
    }

    /**
     * @return bool
     */
    private function useParentThumbnail()
    {
        $thumbnailSource = $this->scopeConfig->getValue(ConfigurableRenderer::CONFIG_THUMBNAIL_SOURCE);
        return $thumbnailSource == ThumbnailSource::OPTION_USE_PARENT_IMAGE;
    }
}
