<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\FieldEntityAttributesInterface;

/**
 * Retrieve filterable attributes for Category queries
 */
class CategoryFilterAttributesForAst implements FieldEntityAttributesInterface
{
    /**
     * Map schema fields to entity attributes
     *
     * @var array
     */
    private $fieldMapping = [
        'ids' => 'entity_id'
    ];

    /**
     * @var array
     */
    private $additionalFields = [
        'is_active'
    ];

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     * @param array $additionalFields
     * @param array $attributeFieldMapping
     */
    public function __construct(
        ConfigInterface $config,
        array $additionalFields = [],
        array $attributeFieldMapping = []
    ) {
        $this->config = $config;
        $this->additionalFields = array_merge($this->additionalFields, $additionalFields);
        $this->fieldMapping = array_merge($this->fieldMapping, $attributeFieldMapping);
    }

    /**
     * @inheritdoc
     *
     * Gather attributes for Category filtering
     * Example format ['attributeNameInGraphQl' => ['type' => 'String'. 'fieldName' => 'attributeNameInSearchCriteria']]
     *
     * @return array
     */
    public function getEntityAttributes() : array
    {
        $categoryFilterType = $this->config->getConfigElement('CategoryFilterInput');

        if (!$categoryFilterType) {
            throw new \LogicException(__("CategoryFilterInput type not defined in schema."));
        }

        $fields = [];
        foreach ($categoryFilterType->getFields() as $field) {
            $fields[$field->getName()] = [
                'type' => 'String',
                'fieldName' => $this->fieldMapping[$field->getName()] ?? $field->getName(),
            ];
        }

        foreach ($this->additionalFields as $additionalField) {
            $fields[$additionalField] = [
                'type' => 'String',
                'fieldName' => $additionalField,
            ];
        }

        return $fields;
    }
}
