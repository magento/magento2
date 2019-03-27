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
     * Join fields attached to field node to collection's select.
     *
     * @param FieldNode $fieldNode
     * @param AbstractCollection $collection
     * @return void
     */
    public function join(FieldNode $fieldNode, AbstractCollection $collection) : void
    {
        foreach ($this->getQueryFields($fieldNode) as $field) {
            if (!$collection->isAttributeAdded($field)) {
                $collection->addAttributeToSelect($field);
            }
        }
    }

    /**
     * Get an array of queried fields.
     *
     * @param FieldNode $fieldNode
     * @return string[]
     */
    public function getQueryFields(FieldNode $fieldNode)
    {
        if (!isset($this->queryFields[$fieldNode->name->value])) {
            $this->queryFields[$fieldNode->name->value] = [];
            $query = $fieldNode->selectionSet->selections;
            /** @var FieldNode $field */
            foreach ($query as $field) {
                if ($field->kind === 'InlineFragment') {
                    continue;
                }
                $this->queryFields[$fieldNode->name->value][] = $field->name->value;
            }
        }

        return $this->queryFields[$fieldNode->name->value];
    }
}
