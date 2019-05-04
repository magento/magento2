<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Index (key) processor.
 *
 * @inheritdoc
 */
class Index implements DbDefinitionProcessorInterface
{
    /**
     * Index statement.
     */
    const INDEX_KEY_NAME = 'INDEX';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Index constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Index $index
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $index)
    {
        $indexType = $index->getIndexType();
        //There is no matter what connection to use -> so use default one
        $adapter = $this->resourceConnection->getConnection();
        $isFullText = $indexType === \Magento\Framework\Setup\Declaration\Schema\Dto\Index::FULLTEXT_INDEX;
        //index types, that are similar to MySQL ones, can just be uppercased.
        //[FULLTEXT ]INDEX `name` [USING [BTREE|HASH]] (columns)
        return sprintf(
            '%sINDEX %s%s (%s)',
            $isFullText ? 'FULLTEXT ' : '',
            $adapter->quoteIdentifier($index->getName()),
            '', // placeholder for USING HASH|BTREE statement for non-fulltext indexes
            implode(
                ',',
                array_map(
                    function ($columnName) use ($adapter) {
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
