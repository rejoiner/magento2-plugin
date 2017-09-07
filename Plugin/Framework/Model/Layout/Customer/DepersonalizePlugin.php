<?php

namespace Rejoiner\Acr\Plugin\Framework\Model\Layout\Customer;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    const REGISTRY_KEY = 'current_customer';

    /** @var \Magento\PageCache\Model\DepersonalizeChecker $depersonalizeChecker*/
    protected $depersonalizeChecker;

    /** @var \Magento\Customer\Model\Session $customerSession */
    protected $customerSession;

    /** @var \Magento\Framework\Registry $customerSession */
    protected $registry;

    /**
     * DepersonalizePlugin constructor.
     * @param \Magento\PageCache\Model\DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\PageCache\Model\DepersonalizeChecker $depersonalizeChecker,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->registry             = $registry;
        $this->customerSession      = $customerSession;
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
            $this->registry->register(self::REGISTRY_KEY, $this->customerSession->getCustomer());
        }

        return $result;
    }
}
