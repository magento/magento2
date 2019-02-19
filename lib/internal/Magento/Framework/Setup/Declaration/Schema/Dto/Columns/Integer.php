<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Integer column.
 * Declared in SQL, like INT(11) or BIGINT(20).
 * Where digit is padding, how many zeros should be added before first non-zero digit.
 */
class Integer extends Column implements
    ElementDiffAwareInterface,
    ColumnUnsignedAwareInterface,
    ColumnNullableAwareInterface,
    ColumnIdentityAwareInterface,
    ColumnDefaultAwareInterface
{
    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var int
     */
    private $default;

    /**
     * @var bool
     */
    private $unsigned;
    /**
     * @var int
     */
    private $padding;
    /**
     * @var bool
     */
    private $identity;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param int $padding
     * @param bool $nullable
     * @param bool $unsigned
     * @param bool $identity
     * @param float|int $default
     * @param string|null $comment
     * @param string|null $onCreate
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        int $padding,
        bool $nullable = true,
        bool $unsigned = false,
        bool $identity = false,
        int $default = null,
        string $comment = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type, $table, $comment, $onCreate);
        $this->nullable = $nullable;
        $this->default = $default;
        $this->unsigned = $unsigned;
        $this->padding = $padding;
        $this->identity = $identity;
    }

    /**
     * Column padding.
     *
     * @return int
     */
    public function getPadding()
    {
        return $this->padding;
    }

    /**
     * Check whether column can be nullable.
     *
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * Return default value.
     * Note: default value should be int.
     *
     * @return int | null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Check whether element is unsigned or not.
     *
     * @return bool
     */
    public function isUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * Define whether column can be autoincrement or not.
     *
     * @return bool
     */
    public function isIdentity()
    {
        return $this->identity;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'nullable' => $this->isNullable(),
            'padding' => $this->getPadding(),
            'unsigned' => $this->isUnsigned(),
            'identity' => $this->isIdentity(),
            'default' => $this->getDefault(),
            'comment' => $this->getComment()
        ];
    }
}
