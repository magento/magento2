<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use GraphQL\Language\AST\FieldNode;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Joins attributes for provided field node field names.
 */
class AttributesJoiner
{
    /**
     * @var array
     */
    private $queryFields = [];

    /**
     * Field to attribute mapping
     *
     * For fields that are not named the same as their attribute, or require extra attributes to resolve
     * e.g. ['field' => ['attr1', 'attr2'], 'other_field' => ['other_attr']]
     *
     * @var array
     */
    private $fieldToAttributeMap = [];

    /**
     * @param array $fieldToAttributeMap
     */
    public function __construct(array $fieldToAttributeMap = [])
    {
        $this->fieldToAttributeMap = $fieldToAttributeMap;
    }

    /**
     * Join fields attached to field node to collection's select.
     *
     * @param FieldNode $fieldNode
     * @param AbstractCollection $collection
     * @return void
     */
    public function join(FieldNode $fieldNode, AbstractCollection $collection): void
    {
        foreach ($this->getQueryFields($fieldNode) as $field) {
            $this->addFieldToCollection($collection, $field);
        }
    }

    /**
     * Get an array of queried fields.
     *
     * @param FieldNode $fieldNode
     * @return string[]
     */
    public function getQueryFields(FieldNode $fieldNode): array
    {
        if (null === $this->getFieldNodeSelections($fieldNode)) {
            $query = $fieldNode->selectionSet->selections;
            $selectedFields = [];
            /** @var FieldNode $field */
            foreach ($query as $field) {
                if ($field->kind === 'InlineFragment') {
                    continue;
                }
                $selectedFields[] = $field->name->value;
            }
            $this->setSelectionsForFieldNode($fieldNode, $selectedFields);
        }

        return $this->getFieldNodeSelections($fieldNode);
    }

    /**
     * Add field to collection select
     *
     * Add a query field to the collection, using mapped attribute names if they are set
     *
     * @param AbstractCollection $collection
     * @param string $field
     */
    private function addFieldToCollection(AbstractCollection $collection, string $field): void
    {
        $attribute = isset($this->fieldToAttributeMap[$field]) ? $this->fieldToAttributeMap[$field] : $field;

        if (is_array($attribute)) {
            foreach ($attribute as $attributeName) {
                if (!$collection->isAttributeAdded($attributeName)) {
                    $collection->addAttributeToSelect($attributeName);
                }
            }
        } else {
            if (!$collection->isAttributeAdded($attribute)) {
                $collection->addAttributeToSelect($attribute);
            }
        }
    }

    /**
     * Get the fields selections for a query node
     *
     * @param FieldNode $fieldNode
     * @return array|null
     */
    private function getFieldNodeSelections(FieldNode $fieldNode): ?array
    {
        return $this->queryFields[$fieldNode->name->value][$fieldNode->name->loc->start] ?? null;
    }

    /**
     * Set the field selections for a query node
     *
     * Index nodes by name and position so nodes with same name don't collide
     *
     * @param FieldNode $fieldNode
     * @param array $selectedFields
     */
    private function setSelectionsForFieldNode(FieldNode $fieldNode, array $selectedFields): void
    {
        $this->queryFields[$fieldNode->name->value][$fieldNode->name->loc->start] = $selectedFields;
    }
}
