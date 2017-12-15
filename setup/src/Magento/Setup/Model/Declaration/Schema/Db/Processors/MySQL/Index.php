<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
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
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return '';
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
