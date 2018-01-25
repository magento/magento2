<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * Real column
 * Declared in SQL, like FLOAT(S, P) or DECIMAL(S, P)
 * Where S - is scale, P - is precision
 */
class Real extends Column implements
    ElementDiffAwareInterface,
    ColumnUnsignedAwareInterface,
    ColumnNullableAwareInterface,
    ColumnDefaultAwareInterface
{
    /**
     * @var int
     */
    private $precision;

    /**
     * @var int
     */
    private $scale;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @var float
     */
    private $default;

    /**
     * @var bool
     */
    private $unsigned;

    /**
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param int $precision
     * @param int $scale
     * @param bool $nullable
     * @param bool $unsigned
     * @param float $default
     * @param string|null $comment
     * @param string|null $onCreate
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        int $precision,
        int $scale,
        bool $nullable = true,
        bool $unsigned = false,
        float $default = null,
        string $comment = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type, $table, $comment, $onCreate);
        $this->precision = $precision;
        $this->scale = $scale;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->unsigned = $unsigned;
    }

    /**
     * Column precision
     *
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Column scale
     *
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Check whether column can be nullable
     *
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * Return default value
     * Note: default value should be float
     *
     * @return float | null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Check whether element is unsigned or not
     *
     * @return bool
     */
    public function isUnsigned()
    {
        return $this->unsigned;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'nullable' => $this->isNullable(),
            'precision' => $this->getPrecision(),
            'scale' => $this->getScale(),
            'unsigned' => $this->isUnsigned(),
            'default' => $this->getDefault(),
            'comment' => $this->getComment()
        ];
    }
}
