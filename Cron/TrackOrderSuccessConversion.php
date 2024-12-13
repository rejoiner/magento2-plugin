<?php
declare(strict_types=1);
/**
 * Copyright © 2024 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Cron;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Rejoiner\Acr\Helper\Data;
use Rejoiner\Acr\Model\ResourceModel\Acr\CollectionFactory;
use DateTime;

class TrackOrderSuccessConversion
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param OrderFactory $orderFactory
     * @param Data $rejoinerHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private   CollectionFactory $collectionFactory,
        protected OrderFactory $orderFactory,
        protected Data $rejoinerHelper,
        private   OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function trackOrder(): self
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('sent_at', ['null' => true]);
        if (!empty($collection->getSize())
            && $this->rejoinerHelper->getRejoinerApiKey()
            && $this->rejoinerHelper->getRejoinerApiSecret()
        ) {
            foreach ($collection as $successOrder) {
                try {
                    $orderModel = $this->orderRepository->get($successOrder->getId());
                } catch (NoSuchEntityException $e) {
                    $orderModel = $this->orderFactory->create();
                }
                $responseCode = $this->rejoinerHelper->sendInfoToRejoiner($orderModel);
                $successOrder->setResponseCode($responseCode);
                $dateTimeObj = new DateTime('now');
                $successOrder->setSentAt($dateTimeObj->format('Y-m-d H:i:s'));
                $successOrder->save();
            }
        }
        return $this;
    }
}
