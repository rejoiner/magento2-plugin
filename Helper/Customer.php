<?php
/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Rejoiner\Acr\Helper;

class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var \Magento\Customer\Model\Customer $currentCustomer */
    protected $currentCustomer;

    /** @var  \Magento\Framework\ObjectManagerInterface $objectManager */
    protected $objectManager;

    /** @var \Magento\Framework\Locale\Resolver $localeResolver */
    protected $localeResolver;

    /**
     * Customer constructor.
     * @param Data $rejoinerHelper
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        array $data = []
    ) {
        $this->localeResolver  = $localeResolver;
        $this->currentCustomer = $customerSession->getCustomer();
        parent::__construct($context);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerInfo()
    {
        return [
            'age'    => $this->getCustomerAge(),
            'gender' => $this->getGender(),
            'en'     => substr($this->localeResolver->getLocale(), 0, 2),
            'name'   => $this->getCurrentCustomer()->getFirstname(),

        ];
    }

    /**
     * @return int
     * @throws \Exception
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getGender()
    {
        /** @var \Magento\Customer\Model\ResourceModel\Customer $resource */
        $resource = $this->getCurrentCustomer()
            ->getResource();

        $genderText = $resource->getAttribute('gender')
            ->getSource()
            ->getOptionText($this->getCurrentCustomer()->getData('gender'));

        return $genderText ?: '';
    }

    /**
     * @return array
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
