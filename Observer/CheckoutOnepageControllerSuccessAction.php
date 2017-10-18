<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

class CheckoutOnepageControllerSuccessAction implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    private $rejoinerHelper;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone */
    private $timezone;

    /** @var \Rejoiner\Acr\Model\AcrFactory $acrFactory */
    private $acrFactory;

    /** @var \Magento\Sales\Model\OrderFactory $orderFactory */
    private $orderFactory;

    /**
     * CheckoutOnepageControllerSuccessAction constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Rejoiner\Acr\Model\AcrFactory $acrFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Rejoiner\Acr\Model\AcrFactory $acrFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->timezone       = $timezone;
        $this->acrFactory     = $acrFactory;
        $this->orderFactory   = $orderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $lastOrderId = $observer->getEvent()->getData('order_ids');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create()->load($lastOrderId[0]);
        if (!$order->getId()) {
            return $this;
        }
        if ($this->rejoinerHelper->getShouldBeProcessedByCron()) {
            /** @var \Rejoiner\Acr\Model\Acr $acrModel */
            $acrModel = $this->acrFactory->create();
            $acrModel->setOrderId($order->getId())->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp()));
            $acrModel->save();
        } else {
            $this->rejoinerHelper->sendInfoToRejoiner($order);
        }

        return $this;
    }
}