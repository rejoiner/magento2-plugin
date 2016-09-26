<?php
namespace Rejoiner\Acr\Observer;

use \Magento\Framework\HTTP\ZendClientFactory;
use \Rejoiner\Acr\Model\AcrFactory;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Rejoiner\Acr\Helper\Data;

class TrackOrderSuccessConversion
{
    protected $_rejoinerHelper;
    protected $_rejoinerFactory;
    protected $_orderFactory;
    protected $_timezone;


    public function __construct(
        ZendClientFactory $httpClient,
        AcrFactory $rejoinerFactory,
        OrderFactory $orderFactory,
        TimezoneInterface $timezone,
        Data $rejoinerHelper
    ) {
        $this->_rejoinerHelper  = $rejoinerHelper;
        $this->_rejoinerFactory = $rejoinerFactory;
        $this->_orderFactory     = $orderFactory;
        $this->_timezone        = $timezone;
    }


    public function trackOrder()
    {
        $collection = $this->_rejoinerFactory->create()->getResourceCollection();
        $collection->addFieldToFilter('sent_at', ['null' => true]);
        if (!empty($collection->getSize())
            && $this->_rejoinerHelper->getRejoinerApiKey()
            && $this->_rejoinerHelper->getRejoinerApiSecret()
        ) {
            foreach ($collection as $successOrder) {
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