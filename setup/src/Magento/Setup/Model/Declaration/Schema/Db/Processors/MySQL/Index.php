<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Look for all indexes that MySQL table has. Note, that we need to ignore
 * all unique indexes, as this indexes belongs to constraints, as they limit DML queries
 *
 * @inheritdoc
 */
class Index implements DbSchemaProcessorInterface
{
    private static $indexTypeMapping = [
        'FULTEXT' => 'fultext',
        'BTREE' => 'btree',
        'HASH' => 'hash'
    ];

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Index $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        $columnsList = array_map(
            function(Column $column) {
                return $column->getName();
            },
            $element->getColumns()
        );
        //as we used index types, that are similar to MySQL ones, we can just make it upper
        return sprintf(
            '%s %s (%s)',
            strtoupper($element->getElementType()),
            $element->getName(),
            implode(',', $columnsList)
        );
    }

    /**
     * @inheritdoc
     */
    public function canBeApplied(ElementInterface $element)
    {
        return $element instanceof \Magento\Setup\Model\Declaration\Schema\Dto\Index;
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return [
            'indexType' => self::$indexTypeMapping[$data['Index_type']],
            'name' => $data['Key_name'],
            'column' => [
                $data['Column_name'] => $data['Column_name']
            ],
            'type' => 'index'
        ];
    }
}
