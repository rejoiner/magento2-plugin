<?php
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
     * @return string
     */
    public function getCartData()
    {
        $result = '';
        $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
        $order = $this->getOrder();

        if ($order->getId()) {
            $total = $displayPriceWithTax? $order->getGrandTotal() : $order->getSubtotal();
            $result = [
                'cart_value' => $this->rejoinerHelper->convertPriceToCents($total),
                'cart_item_count' => intval($order->getTotalQtyOrdered()),
                'customer_order_number' => $order->getIncrementId(),
                'return_url' => $this->_urlBuilder->getUrl('sales/order/view/', ['order_id' => $order->getIncrementId()])
            ];

            if ($promo = $order->getCouponCode()) {
                $result['promo'] = $this->rejoinerHelper->generateCouponCode();
            }
        }
        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array
     */
    public function getCartItems()
    {
        return $this->itemsData->getCartItems($this->getOrder());
    }

    /**
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