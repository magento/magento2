<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model;

use GraphQL\Language\AST\FieldNode;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * {@inheritdoc}
 */
class AttributesJoiner
{
    /**
     * @param AbstractCollection $collection
     */
    public function join(FieldNode $fieldNode, AbstractCollection $collection)
    {
        $query = $fieldNode->selectionSet->selections;

        /** @var FieldNode $field */
        foreach ($query as $field) {
            if (!$collection->isAttributeAdded($field->name->value)) {
                $collection->addAttributeToSelect($field->name->value);
            }
        }
    }
}
