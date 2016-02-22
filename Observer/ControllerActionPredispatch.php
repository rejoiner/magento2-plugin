<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var $_cookieMetadataFactory \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var $_cookieManager \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $cookieManager;

    /**
     * @var $jsonData \Magento\Framework\Json\Helper\Data
     */
    protected $jsonData;

    /**
     * ControllerActionPredispatch constructor.
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Data $jsonData
     * @param PhpCookieManager $cookieManager
     */
    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        Data $jsonData,
        PhpCookieManager $cookieManager
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager         = $cookieManager;
        $this->jsonData               = $jsonData;

    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->checkIfCacheShouldBeValidated($observer)) {
            $cookiesManager = $this->cookieManager;
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath('/');
            $sectionDataIds = $this->jsonData->jsonDecode($cookiesManager->getCookie('section_data_ids'));
            if ($sectionDataIds && isset($sectionDataIds->cart)) {
                $sectionDataIds->cart += 1000;
                $cookiesManager->setPublicCookie('section_data_ids', $this->jsonData->jsonEncode($sectionDataIds), $publicCookieMetadata);
            }
        }
    }

    /**
     * @param Observer $observer
     * @return bool
     */
    protected function checkIfCacheShouldBeValidated(Observer $observer)
    {
        $request = $observer->getEvent()->getData('request');
        return (
            $request->getModuleName()        == 'checkout'
            && $request->getControllerName() == 'cart'
            && $request->getActionName()     == 'index'
            && $request->getParam('updateCart')
        );
    }
}