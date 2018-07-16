<?php
namespace Rejoiner\Acr\Helper;

use Rejoiner\Acr\Plugin\Framework\Model\Layout\Customer\DepersonalizePlugin;

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
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Helper\Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Helper\Context $context,
        array $data = []
    ) {
        $this->localeResolver  = $localeResolver;
        $this->currentCustomer = $registry->registry(DepersonalizePlugin::REGISTRY_KEY);
        parent::__construct($context);
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