<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Magento\Framework\Event\Observer;
use Rejoiner\Acr\Helper\Data;
use Rejoiner\Acr\Model\AcrFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Event\ObserverInterface;

class CheckoutOnepageControllerSuccessAction implements ObserverInterface
{

    /**
     * @var $_rejoinerHelper Data
     */
    protected $rejoinerHelper;

    /**
     * @var $timezone TimezoneInterface
     */
    protected $timezone;

    /**
     * @var $acrFactory AcrFactory
     */
    protected $acrFactory;

    /**
     * @var $orderFactory OrderFactory
     */
    protected $orderFactory;

    /**
     * @var $orderRepository \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * CheckoutOnepageControllerSuccessAction constructor.
     * @param Data $rejoinerHelper
     * @param AcrFactory $acrFactory
     * @param OrderFactory $orderFactory
     * @param TimezoneInterface $timezone
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Data $rejoinerHelper,
        AcrFactory $acrFactory,
        OrderFactory $orderFactory,
        TimezoneInterface $timezone,
        OrderRepository $orderRepository
    ) {
        $this->rejoinerHelper  = $rejoinerHelper;
        $this->timezone        = $timezone;
        $this->acrFactory      = $acrFactory;
        $this->orderFactory    = $orderFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Sends information about success placed order to remote Rejoiner server
     * or creates cronjob for this. There is option in Admin Panel for this.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $lastOrderId = $observer->getEvent()->getData('order_ids');
        $order = $this->orderRepository->get($lastOrderId[0]);
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