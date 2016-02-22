<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Event\ObserverInterface;

class SalesQuoteRemoveItem implements ObserverInterface
{
    /**
     * @var $sessionManager \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * SalesQuoteRemoveItem constructor.
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($quoteItem = $observer->getQuoteItem()) {
            $removedItem[] = $quoteItem->getSku();
            $this->sessionManager->setData(\Rejoiner\Acr\Helper\Data::REMOVED_CART_ITEM_SKU_VARIABLE, $removedItem);
        }
    }
}