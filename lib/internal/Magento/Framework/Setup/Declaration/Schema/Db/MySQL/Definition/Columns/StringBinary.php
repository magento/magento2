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
     * Constant to define the binary data types.
     */
    private const BINARY_TYPES = ['binary', 'varbinary'];

    /**
     * Constant to define the char data types.
     */
    private const CHAR_TYPES = ['char', 'varchar'];

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
     * Get definition for given column.
     *
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
        preg_match($this->getStringBinaryPattern(), $data['definition'], $matches);

        if (array_key_exists('padding', $matches) && !empty($matches['padding'])) {
            $data['length'] = $matches['padding'];
        }

        if (!isset($data['default'])) {
            return $data;
        }

        $isHex = preg_match('`^0x([a-f0-9]+)$`i', $data['default'], $hexMatches);

        if ($this->isBinaryHex($matches['type'], (bool)$isHex)) {
            $data['default'] = hex2bin($hexMatches[1]);
        }

        return $data;
    }

    /**
     * Get the pattern to identify binary and char types.
     *
     * @return string
     */
    private function getStringBinaryPattern(): string
    {
        return sprintf(
            '/^(?<type>%s)\s*\(?(?<padding>\d*)\)?/',
            implode('|', array_merge(self::CHAR_TYPES, self::BINARY_TYPES))
        );
    }

    /**
     * Check if the type is binary and the value is a hex value.
     *
     * @param   string  $type
     * @param   bool    $isHex
     * @return  bool
     */
    private function isBinaryHex($type, bool $isHex): bool
    {
        return in_array($type, self::BINARY_TYPES) && $isHex;
    }
}
