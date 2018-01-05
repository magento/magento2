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
 * Decimal column
 * Declared in SQL, like FLOAT(S, P) or DECIMAL(S, P)
 * Where S - is scale, P - is precission
 */
class Decimal extends Column implements
    ElementDiffAwareInterface,
    ColumnUnsignedAwareInterface,
    ColumnNullableAwareInterface,
    ColumnDefaultAwareInterface
{
    /**
     * @var int
     */
    private $precission;

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
     * @param int $precission
     * @param int $scale
     * @param bool $nullable
     * @param bool $unsigned
     * @param float $default
     * @param string|null $onCreate
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        int $precission,
        int $scale,
        bool $nullable = true,
        bool $unsigned = false,
        float $default = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type, $table, $onCreate);
        $this->precission = $precission;
        $this->scale = $scale;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->unsigned = $unsigned;
    }

    /**
     * Column precission
     *
     * @return int
     */
    public function getPrecission()
    {
        return $this->precission;
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
            'precission' => $this->getPrecission(),
            'scale' => $this->getScale(),
            'unsigned' => $this->isUnsigned(),
            'default' => $this->getDefault()
        ];
    }
}
