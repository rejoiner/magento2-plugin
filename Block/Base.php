<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

class Base extends \Magento\Framework\View\Element\Template
{
    /** @var \Rejoiner\Acr\Helper\Data $rejoinerHelper */
    protected $rejoinerHelper;


    /** @var \Magento\Framework\App\Request\Http $request */
    protected $request;

    /**
     * Base constructor.
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @return \Rejoiner\Acr\Helper\Data
     */
    public function getRejoinerHelper()
    {
        return $this->rejoinerHelper;
    }


    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'BLOCK_TPL',
            $this->_storeManager->getStore()->getCode(),
            $this->getTemplateFile(),
            'base_url' => $this->getBaseUrl(),
            'template' => $this->getTemplate(),
            'page_type' => $this->rejoinerHelper->getCurrentPageName()
        ];
    }

}