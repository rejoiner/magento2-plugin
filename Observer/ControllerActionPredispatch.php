<?php
namespace Rejoiner\Acr\Observer;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{
    private $_cookieMetadataFactory;
    private $_cookieManager;

    public function __construct(
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
    ) {
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager         = $cookieManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($observer->getRequest()->getModuleName() == 'checkout'
            && $observer->getRequest()->getControllerName() == 'cart'
            && $observer->getRequest()->getActionName() == 'index'
            && $observer->getRequest()->getParam('updateCart')
        ) {
            $cookiesManager = $this->_cookieManager;
            $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath('/');
            $sectionDataIds = json_decode($cookiesManager->getCookie('section_data_ids'));
            if ($sectionDataIds && isset($sectionDataIds->cart)) {
                $sectionDataIds->cart += 1000;
                $cookiesManager->setPublicCookie('section_data_ids', json_encode($sectionDataIds), $publicCookieMetadata);
            }
        }

    }
}