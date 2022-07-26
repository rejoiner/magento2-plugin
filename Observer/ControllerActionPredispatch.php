<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{
    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $_cookieMetadataFactory */
    private $_cookieMetadataFactory;

    /** @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager $_cookieManager */
    private $_cookieManager;

    /**
     * ControllerActionPredispatch constructor.
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager
    ) {
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_cookieManager         = $cookieManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getData('request');
        if ($request->getModuleName() == 'checkout'
            && $request->getControllerName() == 'cart'
            && $request->getActionName() == 'index'
            && $request->getParam('updateCart')
        ) {
            $cookiesManager = $this->_cookieManager;
            $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath('/');

            $encodedCookie = $cookiesManager->getCookie('section_data_ids');
            if ($encodedCookie) {
                $sectionDataIds = json_decode($encodedCookie);
                if ($sectionDataIds && isset($sectionDataIds->cart)) {
                    $sectionDataIds->cart += 1000;
                    $cookiesManager->setPublicCookie('section_data_ids', json_encode($sectionDataIds), $publicCookieMetadata);
                }
            }
        }
    }
}