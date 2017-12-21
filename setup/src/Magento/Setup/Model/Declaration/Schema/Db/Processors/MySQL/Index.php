<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Framework\App\ResourceConnection;
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
    /**
     * Key name that is used in requests, like DROP INDEX or ADD INDEX
     */
    const INDEX_KEY_NAME = 'INDEX';

    /**
     * @var array
     */
    private static $indexTypeMapping = [
        'FULTEXT' => 'fultext',
        'BTREE' => 'btree',
        'HASH' => 'hash'
    ];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Index $element
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        $adapter = $this->resourceConnection->getConnection(
            $element->getTable()->getResource()
        );
        $columnsList = array_map(
            function(Column $column) use ($adapter) {
                return $adapter->quoteIdentifier($column->getName());
            },
            $element->getColumns()
        );
        //as we used index types, that are similar to MySQL ones, we can just make it upper
        return sprintf(
            '(%s)',
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
