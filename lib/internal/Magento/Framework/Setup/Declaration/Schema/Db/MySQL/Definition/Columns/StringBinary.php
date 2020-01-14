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
 * Processor for following types: char, varchar, varbinary, binary.
 *
 * @inheritdoc
 */
class StringBinary implements DbDefinitionProcessorInterface
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
     * @param Nullable $nullable
     * @param ResourceConnection $resourceConnection
     * @param Comment $comment
     */
    public function __construct(Nullable $nullable, ResourceConnection $resourceConnection, Comment $comment)
    {
        $this->nullable = $nullable;
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\StringBinary $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        if ($column->getDefault() !== null) {
            $default = sprintf('DEFAULT "%s"', $column->getDefault());
        } else {
            $default = '';
        }

        return sprintf(
            '%s %s(%s) %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType(),
            $column->getLength(),
            $this->nullable->toDefinition($column),
            $default,
            $this->comment->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(char|binary|varchar|varbinary)\s*\((\d+)\)/', $data['definition'], $matches)) {
            $data['length'] = $matches[2];
        }

        return $data;
    }
}
