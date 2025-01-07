<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\Quote\Item;
use Rejoiner\Acr\Helper\Data;

class SalesQuoteRemoveItem implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * SalesQuoteRemoveItem constructor.
     * @param SessionManager $sessionManager
     */
    public function __construct(
        private SessionManager $sessionManager
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Item $quoteItem */
        if ($quoteItem = $observer->getData('quote_item')) {
            $removedItem[] = $quoteItem->getSku();
            $this->sessionManager->setData(Data::REMOVED_CART_ITEM_SKU_VARIABLE, $removedItem);
        }
    }
}
