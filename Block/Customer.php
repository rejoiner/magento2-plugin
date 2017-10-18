<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

use Rejoiner\Acr\Plugin\Framework\Model\Layout\Customer\DepersonalizePlugin;

class Customer extends Base
{
    /** @var \Magento\Customer\Model\Customer $currentCustomer */
    protected $currentCustomer;

    /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
    protected $objectManager;

    /**
     * Customer constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->currentCustomer = $registry->registry(DepersonalizePlugin::REGISTRY_KEY);
        parent::__construct(
            $rejoinerHelper,
            $jsonHelper,
            $imageHelper,
            $checkoutSession,
            $categoryCollectionFactory,
            $localeResolver,
            $registry,
            $context,
            $data
        );
    }

    /**
     * @return string
     */
    public function getCustomerInfo()
    {
        /** @var array $customerData */
        $customerData = [
            'age'    => $this->getCustomerAge(),
            'gender' => $this->getGender(),
            'en'     => substr($this->localeResolver->getLocale(), 0, 2),
            'name'   => $this->getCurrentCustomer()->getFirstname(),

        ];

        return $customerData;
    }

    /**
     * @return int
     */
    protected function getCustomerAge()
    {
        $age = 0;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->getCurrentCustomer();
        if ($dob = $customer->getDob()) {
            $birthdayDate = new \DateTime($dob);
            $now = new \DateTime();
            $interval = $now->diff($birthdayDate);
            $age = $interval->y;
        }
        return $age;
    }

    /**
     * @return string
     */
    protected function getGender()
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer $resource */
        $resource = $this->getCurrentCustomer()
            ->getResource();

        $genderText = $resource->getAttribute('gender')
            ->getSource()
            ->getOptionText($this->getCurrentCustomer()->getData('gender'));

        return $genderText? $genderText : '';
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getCurrentCustomer()->getId() ? parent::_toHtml() : '';
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return ['email' => $this->getCurrentCustomer()->getEmail()];
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer()
    {
        return $this->currentCustomer;
    }
}