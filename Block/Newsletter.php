<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Rejoiner\Acr\Helper\Data;

class Newsletter extends Template
{
    private const DEFAULT_LABEL = 'Sign Up for Newsletter';

    /**
     * @var string
     */
    protected string $label = '';

    /**
     * @var string
     */
    protected string $cssClass = '';

    /**
     * @var string
     */
    protected string $styles = '';

    /**
     * @var array
     */
    protected array $checkboxSelectors = [
        'body .newsletter [name=is_subscribed]',
        'body .form-newsletter-manage #subscription'
    ];

    /**
     * @var ?Data
     */
    protected ?Data $rejoinerHelper;

    /**
     * Newsletter constructor.
     * @param Data $rejoinerHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $rejoinerHelper,
        Context $context,
        array $data = []
    ) {
        $this->rejoinerHelper = $rejoinerHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->rejoinerHelper->getRejoinerMarketingPermissions();
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        if (!$this->label) {
            $label = $this->rejoinerHelper->getRejoinerSubscribeCheckboxLabel();
            $this->label = $label ? : __(self::DEFAULT_LABEL);
        }

        return $this->label;
    }

    /**
     * @return bool
     */
    public function isLabelChanged(): bool
    {
        return $this->getLabel() != self::DEFAULT_LABEL;
    }

    /**
     * @return string
     */
    public function getCssClass(): string
    {
        if (!$this->cssClass) {
            $this->cssClass = $this->rejoinerHelper->getRejoinerSubscribeCheckboxSelector();
        }

        return $this->cssClass;
    }

    /**
     * @return string
     */
    public function getStyles(): string
    {
        if (!$this->styles) {
            $this->styles = $this->rejoinerHelper->getRejoinerSubscribeCheckboxStyle();
        }

        return $this->styles;
    }

    /**
     * @return string
     */
    public function getCheckboxSelectors(): string
    {
        return implode(',', $this->checkboxSelectors);
    }

    /**
     * @return bool
     */
    public function hideInCustomerAccount(): bool
    {
        return !$this->rejoinerHelper->getRejoinerSubscribeCustomerAccount();
    }

    /**
     * @return bool
     */
    public function showOnLoginCheckout(): bool
    {
        return $this->rejoinerHelper->getRejoinerSubscribeLoginCheckout();
    }

    /**
     * @return bool
     */
    public function showOnGuestCheckout(): bool
    {
        return $this->rejoinerHelper->getRejoinerSubscribeGuestCheckout();
    }

    /**
     * @return bool
     */
    public function shouldBeCheckedByDefault(): bool
    {
        return $this->rejoinerHelper->getRejoinerSubscribeCheckedDefault();
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'label' => $this->getLabel() ? : self::DEFAULT_LABEL,
            'show_on_guest_checkout'   => (int) $this->showOnGuestCheckout(),
            'show_on_login_checkout'   => (int) $this->showOnLoginCheckout(),
            'checked_by_default'       => (int) $this->shouldBeCheckedByDefault(),
            'subscribe_guest_checkout' => (int) ($this->showOnGuestCheckout() && $this->shouldBeCheckedByDefault())
        ];
    }
}
