<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

class Snippets extends Base
{
    /**
     * @return string
     */
    public function getCartData()
    {
        $result = '';
        $displayPriceWithTax = $this->rejoinerHelper->getTrackPriceWithTax();
        if ($quote = $this->checkoutSession->getQuote()) {
            $total = $displayPriceWithTax? $quote->getGrandTotal() : $quote->getSubtotal();
            $result = [
                'total_items_count' => (string) intval($quote->getItemsQty()),
                'cart_value'        => (string) $this->rejoinerHelper->convertPriceToCents($total),
                'return_url'        => (string) $this->rejoinerHelper->getRestoreUrl()
            ];
            if ($this->rejoinerHelper->getIsEnabledCouponCodeGeneration()) {
                $result['promo'] = $this->rejoinerHelper->generateCouponCode();
            }

        }

        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $rejoinerHelper = $this->getRejoinerHelper();
        if ($rejoinerHelper->getRejoinerSiteId() && $rejoinerHelper->getDomain() && $this->getCartItems()) {
            $html = parent::_toHtml();
        }

        return $html;
    }
}