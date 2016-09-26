<?php

namespace Rejoiner\Acr\Observer;

use \Magento\Framework\Event\Observer;
use \Rejoiner\Acr\Helper\Data;
use \Rejoiner\Acr\Model\AcrFactory;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckoutOnepageControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{

    private $_rejoinerHelper;
    private $_timezone;
    private $_acrFactory;
    private $_orderFactory;

    public function __construct(
        Data $rejoinerHelper,
        AcrFactory $acrFactory,
        OrderFactory $orderFactory,
        TimezoneInterface $timezone
    ) {
        $this->_rejoinerHelper  = $rejoinerHelper;
        $this->_timezone        = $timezone;
        $this->_acrFactory      = $acrFactory;
        $this->_orderFactory    = $orderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */

    public function execute(Observer $observer)
    {
        $lastOrderId = $observer->getEvent()->getData('order_ids');
        $order = $this->_orderFactory->create()->load($lastOrderId[0]);
        if (!$order->getId()) {
            return $this;
        }
        if ($this->_rejoinerHelper->getShouldBeProcessedByCron()) {
            $acrModel = $this->_acrFactory->create();
            $acrModel->setOrderId($order->getId())->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->_timezone->scopeTimeStamp()));
            $acrModel->save();
        } else {
            $this->_rejoinerHelper->sendInfoToRejoiner($order);
        }
        return $this;
    }
}