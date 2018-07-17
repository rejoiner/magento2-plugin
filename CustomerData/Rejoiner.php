<?php

namespace Rejoiner\Acr\CustomerData;

use \Magento\Customer\CustomerData\SectionSourceInterface;

class Rejoiner implements SectionSourceInterface
{

    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;

    /** @var \Rejoiner\Acr\Helper\Snippets $trackingHelper */
    protected $trackingHelper;

    /** @var \Rejoiner\Acr\Helper\Customer $customerHelper */
    protected $customerHelper;

    /**
     * @var \Rejoiner\Acr\Helper\Conversion
     */
    private $conversion;

    /**
     * Rejoiner constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Rejoiner\Acr\Helper\Customer $customerHelper
     * @param \Rejoiner\Acr\Helper\Snippets $trackingHelper
     * @param \Rejoiner\Acr\Helper\Conversion $
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Rejoiner\Acr\Helper\Customer $customerHelper,
        \Rejoiner\Acr\Helper\Snippets $trackingHelper,
        \Rejoiner\Acr\Helper\Conversion $conversion
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

        if ($this->customerHelper->getCurrentCustomer()) {
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
