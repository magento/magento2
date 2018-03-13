<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\ColumnNullableAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

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
