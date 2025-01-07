<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;


use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Rejoiner\Acr\Helper\Data;
use Rejoiner\Acr\Model\AcrFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class CheckoutOnepageControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param Data $rejoinerHelper
     * @param AcrFactory $acrFactory
     * @param OrderFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private Data $rejoinerHelper,
        private AcrFactory $acrFactory,
        private OrderFactory $orderFactory,
        private OrderRepositoryInterface $orderRepository,
        private TimezoneInterface $timezone
    ) {
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $lastOrderId = $observer->getEvent()->getData('order_ids');
        /** @var Order $order */
        $order = $this->orderRepository->get($lastOrderId[0]);
        if (!$order->getId()) {
            return $this;
        }
        if ($this->rejoinerHelper->getShouldBeProcessedByCron()) {
            $acrModel = $this->acrFactory->create();
            $acrModel->setOrderId($order->getId())->setCreatedAt(date('Y-m-d H:i:s', $this->timezone->scopeTimeStamp()));
            $acrModel->save();
        } else {
            $this->rejoinerHelper->sendInfoToRejoiner($order);
        }

        return $this;
    }
}
