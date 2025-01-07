<?php
namespace Rejoiner\Acr\Helper;

use DateMalformedStringException;
use DateTime;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Model\Customer as CurrentCustomer ;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

class Customer extends AbstractHelper
{
    /** @var CurrentCustomer $currentCustomer */
    protected CurrentCustomer $currentCustomer;

    /**
     * @param Resolver $localeResolver
     * @param SessionManagerInterface $session
     * @param CustomerResource $customerResource
     * @param Context $context
     */
    public function __construct(
        protected Resolver $localeResolver,
        protected SessionManagerInterface $session,
        private CustomerResource $customerResource,
        Context $context
    ) {
        $this->currentCustomer = $session->getCustomer();
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
    protected function getCustomerAge(): int
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
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getGender(): bool|string
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
     * @return CurrentCustomer
     */
    public function getCurrentCustomer(): CurrentCustomer
    {
        return $this->currentCustomer;
    }
}
