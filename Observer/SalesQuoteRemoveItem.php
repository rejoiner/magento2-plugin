<?php
namespace Rejoiner\Acr\Observer;

class SalesQuoteRemoveItem implements \Magento\Framework\Event\ObserverInterface
{
    private $_sessionManager;

    public function __construct(
        \Magento\Framework\Session\SessionManager $sessionManager
    ) {
        $this->_sessionManager = $sessionManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($quote = $observer->getQuoteItem()) {
            $quote->getSku();
            $removedItem[] = $quote->getSku();
            $this->_sessionManager->setData(\Rejoiner\Acr\Helper\Data::REMOVED_CART_ITEM_SKU_VARIABLE, $removedItem);
        }
    }
}