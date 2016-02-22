<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use \Magento\Framework\HTTP\ZendClientFactory;
use \Rejoiner\Acr\Model\AcrFactory;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Rejoiner\Acr\Helper\Data;

class TrackOrderSuccessConversion
{
    /**
     * @var $_rejoinerHelper Data
     */
    protected $_rejoinerHelper;

    /**
     * @var $_rejoinerFactory AcrFactory
     */
    protected $_rejoinerFactory;

    /**
     * @var $_orderFactory OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var $_timezone TimezoneInterface
     */
    protected $_timezone;

    /**
     * TrackOrderSuccessConversion constructor.
     * @param ZendClientFactory $httpClient
     * @param AcrFactory $rejoinerFactory
     * @param OrderFactory $orderFactory
     * @param TimezoneInterface $timezone
     * @param Data $rejoinerHelper
     */
    public function __construct(
        ZendClientFactory $httpClient,
        AcrFactory $rejoinerFactory,
        OrderFactory $orderFactory,
        TimezoneInterface $timezone,
        Data $rejoinerHelper
    ) {
        $this->_rejoinerHelper  = $rejoinerHelper;
        $this->_rejoinerFactory = $rejoinerFactory;
        $this->_orderFactory    = $orderFactory;
        $this->_timezone        = $timezone;
    }

    /**
     * Send success placed orders information to Rejoiner service
     *
     * @return $this
     */
    public function trackOrder()
    {
        /** @var \Rejoiner\Acr\Model\Resource\Acr\Collection $collection */
        $collection = $this->_rejoinerFactory->create()->getResourceCollection();
        $collection->addFieldToFilter('sent_at', ['null' => true]);
        if ($this->_rejoinerHelper->getRejoinerApiKey()
            && $this->_rejoinerHelper->getRejoinerApiSecret()
            && $collection->count()
        ) {
            foreach ($collection as $successOrder) {
                /** @var \Rejoiner\Acr\Model\Acr $successOrder */
                $orderModel = $this->_orderFactory->create();
                $orderModel->load($successOrder->getOrderId());
                if (!$orderModel->getId()) {
                    continue;
                }
                $responseCode = $this->_rejoinerHelper->sendInfoToRejoiner($orderModel);
                $successOrder->setResponseCode($responseCode);
                $successOrder->setSentAt(strftime('%Y-%m-%d %H:%M:%S', $this->_timezone->scopeTimeStamp()));
                $successOrder->save();
            }
        }
        return $this;
    }

}