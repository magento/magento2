<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * DataBuilder class for \Magento\Tax\Api\Data\TaxClassInterface
 * @codeCoverageIgnore
 */
class TaxClassDataBuilder extends \Magento\Framework\Api\Builder
{
    /**
     * Initialize the builder
     *
     * @param \Magento\Framework\Api\ObjectFactory $objectFactory
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string|null $modelClassInterface
     */
    public function __construct(
        \Magento\Framework\Api\ObjectFactory $objectFactory,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = null
    ) {
        parent::__construct(
            $objectFactory,
            $metadataService,
            $attributeValueBuilder,
            $objectProcessor,
            $typeProcessor,
            $dataBuilderFactory,
            $objectManagerConfig,
            'Magento\Tax\Api\Data\TaxClassInterface'
        );
    }

    /**
     * @param int|null $classId
     * @return $this
     */
    public function setClassId($classId)
    {
        $this->_set('class_id', $classId);
        return $this;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        $this->_set('class_name', $className);
        return $this;
    }

    /**
     * @param string $classType
     * @return $this
     */
    public function setClassType($classType)
    {
        $this->_set('class_type', $classType);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $object = parent::create();
        $object->setDataChanges(true);
        return $object;
    }
}
