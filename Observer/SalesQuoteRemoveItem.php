<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

class SalesQuoteRemoveItem implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Magento\Framework\Session\SessionManager $_sessionManager */
    private $_sessionManager;

    /**
     * SalesQuoteRemoveItem constructor.
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     */
    public function __construct(\Magento\Framework\Session\SessionManager $sessionManager)
    {
        $this->_sessionManager = $sessionManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        if ($quoteItem = $observer->getData('quote_item')) {
            $removedItem[] = $quoteItem->getSku();
            $this->_sessionManager->setData(\Rejoiner\Acr\Helper\Data::REMOVED_CART_ITEM_SKU_VARIABLE, $removedItem);
        }
    }
}