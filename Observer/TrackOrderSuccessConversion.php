<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

class TrackOrderSuccessConversion
{
    /** @var \Rejoiner\Acr\Helper\Data $_rejoinerHelper */
    protected $_rejoinerHelper;

    /** @var \Rejoiner\Acr\Model\AcrFactory $_rejoinerFactory */
    protected $_rejoinerFactory;

    /** @var \Magento\Sales\Model\OrderFactory $_orderFactory */
    protected $_orderFactory;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $_timezone */
    protected $_timezone;

    /**
     * TrackOrderSuccessConversion constructor.
     * @param \Rejoiner\Acr\Model\AcrFactory $rejoinerFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     */
    public function __construct(
        \Rejoiner\Acr\Model\AcrFactory $rejoinerFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Rejoiner\Acr\Helper\Data $rejoinerHelper
    ) {
        $this->_rejoinerHelper  = $rejoinerHelper;
        $this->_rejoinerFactory = $rejoinerFactory;
        $this->_orderFactory     = $orderFactory;
        $this->_timezone        = $timezone;
    }

    /**
     * @return $this
     */
    public function trackOrder()
    {
        /** @var \Rejoiner\Acr\Model\ResourceModel\Acr\Collection $collection */
        $collection = $this->_rejoinerFactory->create()->getResourceCollection();
        $collection->addFieldToFilter('sent_at', ['null' => true]);
        if (!empty($collection->getSize())
            && $this->_rejoinerHelper->getRejoinerApiKey()
            && $this->_rejoinerHelper->getRejoinerApiSecret()
        ) {
            foreach ($collection as $successOrder) {
                /** @var \Magento\Sales\Model\Order $orderModel */
                $orderModel = $this->_orderFactory->create();
                $orderModel->load($successOrder->getOrderId());
                $responseCode = $this->_rejoinerHelper->sendInfoToRejoiner($orderModel);
                $successOrder->setResponseCode($responseCode);
                $successOrder->setSentAt(strftime('%Y-%m-%d %H:%M:%S', $this->_timezone->scopeTimeStamp()));
                $successOrder->save();
            }
        }

        return $this;
    }
}