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
 * As all MySQL aliases as BOOL or BOOLEAN are converted to TINYINT(1)
 * proposed to processed tinyint as boolean.
 *
 * @inheritdoc
 */
class Boolean implements DbDefinitionProcessorInterface
{
    /**
     * Type the column is persisted with.
     */
    const TYPE = 'BOOLEAN';

    /**
     * Type of integer that is used in MySQL for boolean.
     */
    const INTEGER_TYPE = 'tinyint';

    /**
     * Padding for integer described below.
     */
    const INTEGER_PADDING = '1';

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
     * Constructor.
     *
     * @param Nullable $nullable
     * @param ResourceConnection $resourceConnection
     * @param Comment $comment
     */
    public function __construct(
        Nullable $nullable,
        ResourceConnection $resourceConnection,
        Comment $comment
    ) {
        $this->nullable = $nullable;
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
     * Get definition for given column.
     *
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return sprintf(
            '%s %s %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            self::TYPE,
            $this->nullable->toDefinition($column),
            $column->getDefault() !== null ?
                sprintf('DEFAULT %s', $column->getDefault() ? 1 : 0) : '',
            $this->comment->toDefinition($column)
        );
    }

    /**
     * Boolean is presented as tinyint(1).
     *
     * @param  array $data
     * @return array
     */
    public function fromDefinition(array $data)
    {
        if ($data['type'] === self::INTEGER_TYPE &&
            (
                array_key_exists('padding', $data) &&
                $data['padding'] === self::INTEGER_PADDING
            )
        ) {
            $data['type'] = strtolower(self::TYPE);
            if (isset($data['default'])) {
                $data['default'] = $data['default'] === null ? null : (bool) $data['default'];
            }
            $data['unsigned'] = false; //Not signed for boolean
            unset($data['padding']);
        }

        return $data;
    }
}
