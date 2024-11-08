<?php
namespace Rejoiner\Acr\Helper;

use DateMalformedStringException;
use DateTime;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\ObjectManagerInterface;

class Customer extends AbstractHelper
{
    /** @var \Magento\Customer\Model\Customer $currentCustomer */
    protected \Magento\Customer\Model\Customer $currentCustomer;

    /** @var  ObjectManagerInterface $objectManager */
    protected ObjectManagerInterface $objectManager;

    /** @var Resolver $localeResolver */
    protected Resolver $localeResolver;

    private \Magento\Customer\Model\ResourceModel\Customer $customerResource;

    /**
     * @param Resolver $localeResolver
     * @param SessionManagerInterface $session
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param Context $context
     */
    public function __construct(
        Resolver $localeResolver,
        SessionManagerInterface $session,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        Context $context
    ) {
        $this->localeResolver  = $localeResolver;
        $this->currentCustomer = $session->getCustomer();
        $this->customerResource = $customerResource;

        parent::__construct($context);
    }

    /**
     * @return array
     * @throws DateMalformedStringException
     */
    public function getCustomerInfo(): array
    {
        return [
            'age'    => $this->getCustomerAge(),
            'gender' => $this->getGender(),
            'en'     => substr($this->localeResolver->getLocale(), 0, 2),
            'name'   => $this->getCurrentCustomer()->getFirstname(). ' ' .$this->getCurrentCustomer()->getLastname()
        ];
    }

    /**
     * @return int
     * @throws DateMalformedStringException
     */
    protected function getCustomerAge()
    {
        $age = 0;
        $customer = $this->getCurrentCustomer();

        if ($dob = $customer->getDob()) {
            $birthdayDate = new DateTime($dob);
            $now = new DateTime();
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
        $genderText = $this->customerResource->getAttribute('gender')
            ->getSource()
            ->getOptionText($this->getCurrentCustomer()->getData('gender'));

        return $genderText? : '';
    }

    /**
     * @return array
     */
    public function getCustomerEmail(): array
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
