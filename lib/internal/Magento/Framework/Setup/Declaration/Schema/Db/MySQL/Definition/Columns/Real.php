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
 * Process real types and separate them into type, scale and precision.
 * See https://dev.mysql.com/doc/refman/5.7/en/precision-math-decimal-characteristics.html
 *
 * @inheritdoc
 */
class Real implements DbDefinitionProcessorInterface
{
    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var Unsigned
     */
    private $unsigned;

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
     * @param Unsigned $unsigned
     * @param Comment $comment
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Nullable $nullable,
        Unsigned $unsigned,
        Comment $comment,
        ResourceConnection $resourceConnection
    ) {
        $this->nullable = $nullable;
        $this->unsigned = $unsigned;
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        if ($column->getPrecision() === 0 && $column->getScale() === 0) {
            $type = $column->getType();
        } else {
            $type = sprintf('%s(%s, %s)', $column->getType(), $column->getPrecision(), $column->getScale());
        }

        return sprintf(
            '%s %s %s %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $type,
            $this->unsigned->toDefinition($column),
            $this->nullable->toDefinition($column),
            $column->getDefault() !== null ?
                sprintf('DEFAULT %s', $column->getDefault()) : '',
            $this->comment->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(float|decimal|double)\s*\((\d+)\s*,\s*(\d+)\)/i', $data['definition'] ?? '', $matches)) {
            /**
             * match[1] - type
             * match[2] - precision
             * match[3] - scale
             */
            $data['precision'] = $matches[2];
            $data['scale'] = $matches[3];
            $data = $this->nullable->fromDefinition($data);
            $data = $this->unsigned->fromDefinition($data);
        } elseif (preg_match('/^decimal\s*\(\s*(\d+)\s*\)/i', $data['definition'] ?? '', $matches)) {
            $data['precision'] = $matches[1];
            $data['scale'] = 0;
        }

        return $data;
    }
}
