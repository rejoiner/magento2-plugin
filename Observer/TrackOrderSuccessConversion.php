<?php
namespace Rejoiner\Acr\Observer;

class TrackOrderSuccessConversion
{
    protected $_rejoinerHelper;
    protected $_rejoinerFactory;
    protected $_orderFactory;
    protected $_timezone;


    public function __construct(
        \Magento\Framework\HTTP\ZendClientFactory $httpClient,
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


    public function trackOrder()
    {
        $collection = $this->_rejoinerFactory->create()->getResourceCollection();
        $collection->addFieldToFilter('sent_at', ['null' => true]);
        if ($collection->count()
            && $this->_rejoinerHelper->getStoreConfig(\Rejoiner\Acr\Helper\Data::XML_PATH_REJOINER_API_KEY)
            && $this->_rejoinerHelper->getStoreConfig(\Rejoiner\Acr\Helper\Data::XML_PATH_REJOINER_API_SECRET)
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