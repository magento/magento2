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
 * Process blob and text types.
 *
 * @inheritdoc
 */
class Blob implements DbDefinitionProcessorInterface
{
    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var Comment
     */
    private $comment;

    /**
     * Blob constructor.
     *
     * @param Nullable $nullable
     * @param Comment $comment
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Nullable $nullable,
        Comment $comment,
        ResourceConnection $resourceConnection
    ) {
        $this->nullable = $nullable;
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
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
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^text\s*\((\d+)\)/', $data['definition'], $matches) && isset($matches[1])) {
            $data['length'] = $matches[1];
        }

        return $data;
    }
}
