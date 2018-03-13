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
 * Nullable columns processor.
 *
 * @inheritdoc
 */
class Nullable implements DbDefinitionProcessorInterface
{
    /**
     * @param ColumnNullableAwareInterface $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        if ($column instanceof ColumnNullableAwareInterface) {
            return $column->isNullable() ? 'NULL' : 'NOT NULL';
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
