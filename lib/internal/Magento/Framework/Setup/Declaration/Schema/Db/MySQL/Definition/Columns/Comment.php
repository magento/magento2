<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\ColumnNullableAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Comment definition processor.
 *
 * @inheritdoc
 */
class Comment implements DbDefinitionProcessorInterface
{
    /**
     * @param Column $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return $column->getComment() !== null ? sprintf('COMMENT "%s"', $column->getComment()) : '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return $data;
    }
}
