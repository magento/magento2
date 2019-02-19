<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Date type processor.
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
     * @var Nullable
     */
    private $nullable;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param Nullable $nullable
     * @param Comment $comment
     */
    public function __construct(ResourceConnection $resourceConnection, Nullable $nullable, Comment $comment)
    {
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
        $this->nullable = $nullable;
    }

    /**
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return sprintf(
            '%s %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType(),
            $this->nullable->toDefinition($column),
            $this->comment->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function fromDefinition(array $data)
    {
        return $data;
    }
}
