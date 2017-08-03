<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;

/**
 * Class \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver
 *
 * @since 2.1.0
 */
class FieldMapperResolver implements FieldMapperInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    private $objectManager;

    /**
     * @var string[]
     * @since 2.1.0
     */
    private $fieldMappers;

    /**
     * Field Mapper instance
     *
     * @var FieldMapperInterface
     * @since 2.1.0
     */
    private $fieldMapperEntity;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $fieldMappers
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $fieldMappers = []
    ) {
        $this->objectManager = $objectManager;
        $this->fieldMappers = $fieldMappers;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        return $this->getEntity($entityType)->getFieldName($attributeCode, $context);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getAllAttributesTypes($context = [])
    {
        $entityType = isset($context['entityType']) ? $context['entityType'] : Config::ELASTICSEARCH_TYPE_DEFAULT;
        return $this->getEntity($entityType)->getAllAttributesTypes($context);
    }

    /**
     * Get instance of current field mapper
     *
     * @param string $entityType
     * @return FieldMapperInterface
     * @throws \Exception
     * @since 2.1.0
     */
    private function getEntity($entityType)
    {
        if (empty($this->fieldMapperEntity)) {
            if (empty($entityType)) {
                throw new \Exception(
                    'No entity type given'
                );
            }
            if (!isset($this->fieldMappers[$entityType])) {
                throw new \LogicException(
                    'There is no such field mapper: ' . $entityType
                );
            }
            $fieldMapperClass = $this->fieldMappers[$entityType];
            $this->fieldMapperEntity = $this->objectManager->create($fieldMapperClass);
            if (!($this->fieldMapperEntity instanceof FieldMapperInterface)) {
                throw new \InvalidArgumentException(
                    'Field mapper must implement \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface'
                );
            }
        }
        return $this->fieldMapperEntity;
    }
}
