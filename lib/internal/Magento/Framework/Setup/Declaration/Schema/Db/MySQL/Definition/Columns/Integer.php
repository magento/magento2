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
 * Integer type processor.
 *
 * Processes integer type and separate it on type and padding.
 *
 * @inheritdoc
 */
class Integer implements DbDefinitionProcessorInterface
{
    /**
     * @var Unsigned
     */
    private $unsigned;

    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Boolean
     */
    private $boolean;

    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var Identity
     */
    private $identity;

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
     * @param Unsigned $unsigned
     * @param bool $boolean
     * @param Nullable $nullable
     * @param Identity $identity
     * @param Comment $comment
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Unsigned $unsigned,
        Boolean $boolean,
        Nullable $nullable,
        Identity $identity,
        Comment $comment,
        ResourceConnection $resourceConnection
    ) {
        $this->unsigned = $unsigned;
        $this->boolean = $boolean;
        $this->nullable = $nullable;
        $this->identity = $identity;
        $this->resourceConnection = $resourceConnection;
        $this->comment = $comment;
    }

    /**
     * @inheritdoc
     * @param \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer $column
     */
    public function toDefinition(ElementInterface $column)
    {
        $definition = sprintf(
            '%s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            $column->getType()
        );

        return sprintf(
            '%s %s %s %s %s %s',
            $definition,
            $this->unsigned->toDefinition($column),
            $this->nullable->toDefinition($column),
            $column->getDefault() !== null ?
                sprintf('DEFAULT %s', (string) (int)$column->getDefault()) : '',
            $this->identity->toDefinition($column),
            $this->comment->toDefinition($column)
        );
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match(
            '/^(?<type>(?:big|small|tiny|medium)?int)(?:\((?<padding>\d+)\))?/',
            $data['definition'] ?? '',
            $matches
        )) {
            // we have an agreement that tinyint(1) is Boolean
            if (isset($matches['padding'])
                && $matches['type'] === 'tinyint'
                && $matches['padding'] === '1'
            ) {
                $data['padding'] = $matches['padding'];
            }
            $data = $this->unsigned->fromDefinition($data);
            $data = $this->nullable->fromDefinition($data);
            $data = $this->identity->fromDefinition($data);
            $data = $this->boolean->fromDefinition($data);
        }

        return $data;
    }
}
