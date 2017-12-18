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
 * Integer column
 * Declared in SQL, like INT(11) or BIGINT(20)
 * Where digit is padding, how many zeros should be added before first non-zero digit
 */
class Integer extends Column implements ElementDiffAwareInterface
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
     * @param string $name
     * @param string $elementType
     * @param Table $table
     * @param int $padding
     * @param bool $nullable
     * @param bool $unsigned
     * @param bool $identity
     * @param float|int $default
     */
    public function __construct(
        string $name,
        string $elementType,
        Table $table,
        int $padding,
        bool $nullable = true,
        bool $unsigned = false,
        bool $identity = false,
        int $default = null
    ) {
        parent::__construct($name, $elementType, $table);
        $this->nullable = $nullable;
        $this->default = $default;
        $this->unsigned = $unsigned;
        $this->padding = $padding;
        $this->identity = $identity;
    }

    /**
     * Column padding
     *
     * @return int
     */
    public function getPadding()
    {
        return $this->padding;
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
     * Note: default value should be int
     *
     * @return int | null
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
     * Define whether column can be autoincremented or not
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
            'type' => $this->getElementType(),
            'nullable' => $this->isNullable(),
            'padding' => $this->getPadding(),
            'unsigned' => $this->isUnsigned(),
            'identity' => $this->isIdentity(),
            'default' => $this->getDefault()
        ];
    }
}
