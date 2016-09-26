<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

class Conversion extends Base
{
    /**
     * @return string
     */
    public function getCartData()
    {
        $result = '';
        $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
        if ($quote = $this->getQuote()) {
            $total = $displayPriceWithTax? $quote->getGrandTotal() : $quote->getSubtotal();
            $result = [
                'cart_value' => $this->rejoinerHelper->convertPriceToCents($total),
                'cart_item_count' => intval($quote->getItemsQty()),
                'customer_order_number' => $this->checkoutSession->getLastRealOrderId(),
                'return_url' => $this->_urlBuilder->getUrl('sales/order/view/', ['order_id' => $this->checkoutSession->getLastOrderId()])
            ];
            if ($promo = $quote->getCouponCode()) {
                $result['promo'] = $this->rejoinerHelper->generateCouponCode();
            }
        }
        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quoteId = $this->checkoutSession->getLastQuoteId()) {
            $quote->load($quoteId);
        }
        return $quote;
    }
}