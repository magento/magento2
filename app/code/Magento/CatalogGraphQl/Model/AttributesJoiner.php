<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NodeKind;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

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
     * @param ResolveInfo $resolverInfo
     * @return void
     */
    public function join(FieldNode $fieldNode, AbstractCollection $collection, ResolveInfo $resolverInfo): void
    {
        foreach ($this->getQueryFields($fieldNode, $resolverInfo) as $field) {
            $this->addFieldToCollection($collection, $field);
        }
    }

    /**
     * Get an array of queried fields.
     *
     * @param FieldNode $fieldNode
     * @param ResolveInfo $resolverInfo
     * @return string[]
     */
    public function getQueryFields(FieldNode $fieldNode, ResolveInfo $resolveInfo): array
    {
        if (null === $this->getFieldNodeSelections($fieldNode)) {
            $query = $fieldNode->selectionSet->selections;
            $selectedFields = [];
            /** @var FieldNode $field */
            foreach ($query as $field) {
                if ($field->kind === NodeKind::INLINE_FRAGMENT) {
                    $inlineFragmentFields = $this->addInlineFragmentFields($resolveInfo, $field);
                    $selectedFields = array_merge($selectedFields, $inlineFragmentFields);
                } elseif ($field->kind === NodeKind::FRAGMENT_SPREAD &&
                    ($spreadFragmentNode = $resolveInfo->fragments[$field->name->value])) {

                    foreach ($spreadFragmentNode->selectionSet->selections as $spreadNode) {
                        if (isset($spreadNode->selectionSet->selections)) {
                            $fragmentSpreadFields = $this->getQueryFields($spreadNode, $resolveInfo);
                            $selectedFields = array_merge($selectedFields, $fragmentSpreadFields);
                        } else {
                            $selectedFields[] = $spreadNode->name->value;
                        }
                    }
                } else {
                    $selectedFields[] = $field->name->value;
                }
            }
            $this->setSelectionsForFieldNode($fieldNode, array_unique($selectedFields));
        }

        return $this->getFieldNodeSelections($fieldNode);
    }

    /**
     * Add fields from inline fragment nodes
     *
     * @param ResolveInfo $resolveInfo
     * @param InlineFragmentNode $inlineFragmentField
     * @param array $inlineFragmentFields
     * @return string[]
     */
    private function addInlineFragmentFields(
        ResolveInfo $resolveInfo,
        InlineFragmentNode $inlineFragmentField,
        $inlineFragmentFields = []
    ): array {
        $query = $inlineFragmentField->selectionSet->selections;
        /** @var FieldNode $field */
        foreach ($query as $field) {
            if ($field->kind === NodeKind::INLINE_FRAGMENT) {
                $this->addInlineFragmentFields($resolveInfo, $field, $inlineFragmentFields);
            } elseif (isset($field->selectionSet->selections)) {
                if (is_array($queryFields = $this->getQueryFields($field, $resolveInfo))) {
                    $inlineFragmentFields = array_merge($inlineFragmentFields, $queryFields);
                }
            } else {
                $inlineFragmentFields[] = $field->name->value;
            }
        }

        return array_unique($inlineFragmentFields);
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
