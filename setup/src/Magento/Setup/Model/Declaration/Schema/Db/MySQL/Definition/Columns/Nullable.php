<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnNullableAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Go through all columns that can be nullable and modify them
 *
 * @inheritdoc
 */
class Nullable implements DbDefinitionProcessorInterface
{
    /**
     * @param ColumnNullableAwareInterface $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        if ($element instanceof ColumnNullableAwareInterface) {
            return $element->isNullable() ? 'NULL' : 'NOT NULL';
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return $data;
    }
}
