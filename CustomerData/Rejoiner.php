<?php

namespace Rejoiner\Acr\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Rejoiner\Acr\Helper\Conversion;
use Rejoiner\Acr\Helper\Customer;
use Rejoiner\Acr\Helper\Data;
use Rejoiner\Acr\Helper\Snippets;

class Rejoiner implements SectionSourceInterface
{

    /** @var Data $rejoinerHelper */
    protected Data $rejoinerHelper;

    /** @var Snippets $trackingHelper */
    protected Snippets $trackingHelper;

    /** @var Customer $customerHelper */
    protected Customer $customerHelper;

    /** @var Conversion */
    private Conversion $conversion;

    /**
     * Rejoiner constructor.
     * @param Data $rejoinerHelper
     * @param Customer $customerHelper
     * @param Snippets $trackingHelper
     * @param Conversion $conversion
     */
    public function __construct(
        Data $rejoinerHelper,
        Customer $customerHelper,
        Snippets $trackingHelper,
        Conversion $conversion
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->trackingHelper = $trackingHelper;
        $this->customerHelper = $customerHelper;
        $this->conversion = $conversion;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $result = [];

        if ($this->customerHelper->getCurrentCustomer()->getId()) {
            $result['customerData'] = json_encode($this->customerHelper->getCustomerInfo(), JSON_UNESCAPED_SLASHES);
            $result['customerEmail'] = json_encode($this->customerHelper->getCustomerEmail(), JSON_UNESCAPED_SLASHES);
        }

        if ($this->conversion->shouldSaveConversionData()) {
            $result['convertionCartData']  = json_encode($this->conversion->getCartData(), JSON_UNESCAPED_SLASHES);
            $result['convertionCartItems'] = json_encode($this->conversion->getCartItems(), JSON_UNESCAPED_SLASHES);
        } else {
            if ($this->trackingHelper->getCartData()) {
                $result['cartData'] = json_encode($this->trackingHelper->getCartData(), JSON_UNESCAPED_SLASHES);
                $result['cartItems'] = json_encode($this->trackingHelper->getCartItems(), JSON_UNESCAPED_SLASHES);
            }

            if ($removerItem = $this->rejoinerHelper->checkRemovedItem()) {
                $result['removedItems'] = json_encode($removerItem, JSON_UNESCAPED_SLASHES);
            }
        }

        return $result;
    }
}
