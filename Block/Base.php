<?php
declare(strict_types=1);
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Block;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Rejoiner\Acr\Helper\Data;

class Base extends Template
{
    /** @var Data $rejoinerHelper */
    protected Data $rejoinerHelper;


    /** @var Http $request */
    protected Http $request;

    /**
     * Base constructor.
     * @param Data $rejoinerHelper
     * @param Http $request
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $rejoinerHelper,
        Http $request,
        Context $context,
        array $data = []
    ) {
        $this->rejoinerHelper = $rejoinerHelper;
        $this->request = $request;

        parent::__construct($context, $data);
    }

    /**
     * @return Data
     */
    public function getRejoinerHelper(): Data
    {
        return $this->rejoinerHelper;
    }


    /**
     * Get cache key informative items
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo(): array
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
