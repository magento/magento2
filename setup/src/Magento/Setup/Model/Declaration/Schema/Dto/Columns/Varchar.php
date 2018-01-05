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
 * Varchar column
 * Declared in SQL, like VARCHAR(L),
 * where L - length
 */
class Varchar extends Column implements
    ElementDiffAwareInterface,
    ColumnNullableAwareInterface,
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
     * @var int
     */
    private $length;

    /**
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param int $length
     * @param bool $nullable
     * @param string $default
     * @param string|null $onCreate
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        int $length,
        bool $nullable = true,
        string $default = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type, $table, $onCreate);
        $this->nullable = $nullable;
        $this->default = $default;
        $this->length = $length;
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
     * Note: default value should be string
     *
     * @return string | null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Length can be integer value from 0 to 255
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'nullable' => $this->isNullable(),
            'default' => $this->getDefault(),
            'length' => $this->getLength()
        ];
    }
}
