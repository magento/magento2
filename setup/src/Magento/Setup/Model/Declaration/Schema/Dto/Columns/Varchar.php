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
    ColumnNullableAwareInterface
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
     * @param string $elementType
     * @param Table $table
     * @param bool $nullable
     * @param int $length
     * @param float|int $default
     */
    public function __construct(
        string $name,
        string $elementType,
        Table $table,
        int $length,
        bool $nullable = true,
        int $default = null
    ) {
        parent::__construct($name, $elementType, $table);
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
            'type' => $this->getElementType(),
            'nullable' => $this->isNullable(),
            'default' => $this->getDefault(),
            'length' => $this->getLength()
        ];
    }
}
