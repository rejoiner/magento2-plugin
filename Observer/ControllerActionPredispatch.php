<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class ControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     */
    public function __construct(
        private CookieMetadataFactory $cookieMetadataFactory,
        private PhpCookieManager $cookieManager
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function execute(Observer $observer): void
    {
        /** @var RequestInterface $request */
        $request = $observer->getData('request');
        if ($request->getModuleName() == 'checkout'
            && $request->getControllerName() == 'cart'
            && $request->getActionName() == 'index'
            && $request->getParam('updateCart')
        ) {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath('/');
            $sectionDataIds = json_decode($this->cookieManager->getCookie('section_data_ids'));
            if ($sectionDataIds && isset($sectionDataIds->cart)) {
                $sectionDataIds->cart += 1000;
                $this->cookieManager->setPublicCookie('section_data_ids', json_encode($sectionDataIds), $publicCookieMetadata);
            }
        }
    }
}
