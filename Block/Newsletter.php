<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

class Newsletter extends \Magento\Framework\View\Element\Template
{
    const DEFAULT_LABEL = 'Sign Up for Newsletter';

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $cssClass;

    /**
     * @var string
     */
    protected $styles;

    /**
     * @var array
     */
    protected $checkboxSelectors = [
        'body .newsletter [name=is_subscribed]',
        'body .form-newsletter-manage #subscription'
    ];

    /**
     * @var \Rejoiner\Acr\Helper\Data
     */
    protected $rejoinerHelper;

    /**
     * Newsletter constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->rejoinerHelper->getRejoinerMarketingPermissions();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (!$this->label) {
            $label = $this->rejoinerHelper->getRejoinerSubscribeCheckboxLabel();
            $this->label = $label ? $label : __(self::DEFAULT_LABEL);
        }

        return $this->label;
    }

    /**
     * @return bool
     */
    public function isLabelChanged()
    {
        return $this->getLabel() != self::DEFAULT_LABEL;
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        if (!$this->cssClass) {
            $this->cssClass = $this->rejoinerHelper->getRejoinerSubscribeCheckboxSelector();
        }

        return $this->cssClass;
    }

    /**
     * @return string
     */
    public function getStyles()
    {
        if (!$this->styles) {
            $this->styles = $this->rejoinerHelper->getRejoinerSubscribeCheckboxStyle();
        }

        return $this->styles;
    }

    /**
     * @return string
     */
    public function getCheckboxSelectors()
    {
        return implode(',', $this->checkboxSelectors);
    }

    /**
     * @return bool
     */
    public function hideInCustomerAccount()
    {
        return !$this->rejoinerHelper->getRejoinerSubscribeCustomerAccount();
    }

    /**
     * @return bool
     */
    public function showOnLoginCheckout()
    {
        return $this->rejoinerHelper->getRejoinerSubscribeLoginCheckout();
    }

    /**
     * @return bool
     */
    public function showOnGuestCheckout()
    {
        return $this->rejoinerHelper->getRejoinerSubscribeGuestCheckout();
    }

    /**
     * @return bool
     */
    public function shouldBeCheckedByDefault()
    {
        return $this->rejoinerHelper->getRejoinerSubscribeCheckedDefault();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'label' => $this->getLabel() ? $this->getLabel() : self::DEFAULT_LABEL,
            'show_on_guest_checkout'   => (int) $this->showOnGuestCheckout(),
            'show_on_login_checkout'   => (int) $this->showOnLoginCheckout(),
            'checked_by_default'       => (int) $this->shouldBeCheckedByDefault(),
            'subscribe_guest_checkout' => (int) $this->shouldBeCheckedByDefault()
        ];
    }
}