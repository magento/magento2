<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * Process timestamp and find out it on_update and default values
 *
 * @inheritdoc
 */
class Date implements DbDefinitionProcessorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Comment
     */
    private $comment;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Comment $comment
     */
    public function __construct(ResourceConnection $resourceConnection, Comment $comment)
    {
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return sprintf(
            '%s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType(),
            $this->comment->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        return $data;
    }
}
