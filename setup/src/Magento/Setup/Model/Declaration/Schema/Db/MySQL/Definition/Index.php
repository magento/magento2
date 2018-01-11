<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Look for all indexes that MySQL table has. Note, that we need to ignore
 * all unique indexes, as this indexes belongs to constraints, as they limit DML queries
 *
 * @inheritdoc
 */
class Index implements DbDefinitionProcessorInterface
{
    /**
     * Key name that is used in requests, like DROP INDEX or ADD INDEX
     */
    const INDEX_KEY_NAME = 'INDEX';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Index constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Index $index
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $index)
    {
        $indexType = $index->getIndexType();
        //There is no matter what connection to use -> so use default one
        $adapter = $this->resourceConnection->getConnection();
        $isFullText = $indexType === \Magento\Setup\Model\Declaration\Schema\Dto\Index::FULLTEXT_INDEX;
        //as we used index types, that are similar to MySQL ones, we can just make it upper
        //[FULLTEXT ]INDEX `name` [USING [BTREE|HASH]] (columns)
        return sprintf(
            '%sINDEX %s%s (%s)',
            $isFullText ? 'FULLTEXT ' : '',
            $adapter->quoteIdentifier($index->getName()),
            !$isFullText ? sprintf(' USING %s', strtoupper($indexType)) : '',
            implode(
                ',',
                array_map(
                    function($columnName) use($adapter) {
                        return $adapter->quoteIdentifier($columnName);
                    },
                    $index->getColumnNames()
                )
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return [
            'indexType' => strtolower($data['Index_type']),
            'name' => $data['Key_name'],
            'column' => [
                $data['Column_name'] => $data['Column_name']
            ],
            'type' => 'index'
        ];
    }
}
