<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Rejoiner\Acr\Helper;

use Magento\Sales\Model\Order;

class Conversion extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Data
     */
    private $rejoinerHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var ItemsData
     */
    private $itemsData;

    /**
     * Conversion constructor.
     * @param Data $rejoinerHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param ItemsData $itemsData
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        ItemsData $itemsData,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->rejoinerHelper = $rejoinerHelper;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->itemsData = $itemsData;
    }

    /**
     * ShouldSaveConversionData flag
     *
     * @return int
     */
    public function shouldSaveConversionData()
    {
        $order = $this->getOrder();

        if ($order->getId()
            && null === $this->checkoutSession->getQuoteId()
            && $order->getQuoteId() == $this->checkoutSession->getLastQuoteId()
        ) {
            $result = 1;
        } else {
            $result = 0;
        }

        return $result;
    }

    /**
     * Get Cart Data
     *
     * @return array
     */
    public function getCartData()
    {
        $result = [];
        $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
        $order = $this->getOrder();

        if ($order->getId()) {
            $total = $displayPriceWithTax ? $order->getGrandTotal() : $order->getSubtotal();
            $result = [
                'cart_value' => $this->rejoinerHelper->convertPriceToCents($total),
                'cart_item_count' => (int) $order->getTotalQtyOrdered(),
                'customer_order_number' => $order->getIncrementId(),
                'return_url' => $this->_urlBuilder->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getIncrementId()]
                )
            ];

            $promo = $order->getCouponCode();

            if ($promo) {
                $result['promo'] = $promo;
            }
        }

        return $result;
    }

    /**
     * Get cart items
     *
     * @return array
     */
    public function getCartItems()
    {
        return $this->itemsData->getCartItems($this->getOrder());
    }

    /**
     * Get order
     *
     * @return Order
     */
    private function getOrder()
    {
        if ($this->order === null) {
            /** @var Order $order */
            $this->order = $this->orderFactory->create();
            $this->order->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
        }

        return $this->order;
    }
}
