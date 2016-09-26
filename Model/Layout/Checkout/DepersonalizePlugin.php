<?php
namespace Rejoiner\Acr\Model\Layout\Checkout;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    const REGISTRY_KEY = 'checkout';

    /** @var  \Magento\Quote\Model\Quote $quote */
    protected $quote;


    /** @var \Magento\Framework\Registry $customerSession */
    protected $registry;


    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->registry             = $registry;
        $this->checkoutSession      = $checkoutSession;
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if (!$this->registry->registry(self::REGISTRY_KEY)) {
            $this->registry->register(self::REGISTRY_KEY, $this->checkoutSession->getQuote());
        }
        return $result;
    }
}