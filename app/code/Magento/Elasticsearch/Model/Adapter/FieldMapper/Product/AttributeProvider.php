<?php

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter\DummyAttribute;

/**
 * Provide attribute adapter.
 */
class AttributeProvider
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var array
     */
    private $cachedPool = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Config $eavConfig
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Config $eavConfig,
        $instanceName = 'Magento\\Elasticsearch\\Model\\Adapter\\FieldMapper\\Product\\AttributeAdapter'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $attributeCode
     * @return AttributeAdapter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByAttributeCode(string $attributeCode): AttributeAdapter
    {
        if (!isset($this->cachedPool[$attributeCode])) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
            if (null === $attribute) {
                $attribute = $this->objectManager->create(DummyAttribute::class);
            }
            $this->cachedPool[$attributeCode] = $this->objectManager->create(
                $this->instanceName,
                ['attribute' => $attribute, 'attributeCode' => $attributeCode]
            );
        }

        return $this->cachedPool[$attributeCode];
    }
}
