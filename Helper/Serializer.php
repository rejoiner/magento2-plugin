<?php
/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Helper;

/**
 * Since Magento 2.2.0 encoded data is stored in JSON instead of the serialized PHP array
 * We must support variants for Magento native data like quote item options
 *
 * Class Serializer
 * @package Rejoiner\Acr\Helper
 */
class Serializer extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SERIALIZATION_STRATEGY_SERIALIZE = 'serialize';

    const SERIALIZATION_STRATEGY_JSON = 'json';

    const SERIALIZER_CLASS_JSON = 'Magento\Framework\Serialize\Serializer\Json';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    private $objectManager;

    /**
     * Serializer constructor.
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $data
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     */
    public function decode($data)
    {
        if ($this->getSerializationStrategy() === self::SERIALIZATION_STRATEGY_JSON) {
            /** @var \Magento\Framework\Serialize\Serializer\Json $jsonSerializer */
            $jsonSerializer = $this->objectManager->get(self::SERIALIZER_CLASS_JSON);
            $data = $jsonSerializer->unserialize($data);
        } else {
            // Unserialize arrays only. Do not allow objects creation for security reasons
            $data = unserialize($data, ['allowed_classes' => []]);
        }

        return $data;
    }

    /**
     * Define serialization strategy based on the Magento version
     *
     * @return string
     */
    public function getSerializationStrategy()
    {
        return version_compare($this->productMetadata->getVersion(), '2.2.0', '>=')
            ? self::SERIALIZATION_STRATEGY_JSON
            : self::SERIALIZATION_STRATEGY_SERIALIZE;
    }
}
