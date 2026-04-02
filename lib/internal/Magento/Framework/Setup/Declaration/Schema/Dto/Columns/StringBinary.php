<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Columns;

use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * String or Binary column.
 * Declared in SQL, like CHAR(L), VARCHAR(L), BINARY(L)
 * where L - length.
 */
class StringBinary extends Column implements
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
     * @var string|null
     */
    private $charset;

    /**
     * @var string|null
     */
    private $collation;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param int $length
     * @param bool $nullable
     * @param string $default
     * @param string|null $comment
     * @param string|null $onCreate
     * @param string|null $charset
     * @param string|null $collation
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        int $length,
        bool $nullable = true,
        ?string $default = null,
        ?string $comment = null,
        ?string $onCreate = null,
        ?string $charset = 'utf8mb4',
        ?string $collation = 'utf8mb4_general_ci'
    ) {
        parent::__construct($name, $type, $table, $comment, $onCreate);
        $this->nullable = $nullable;
        $this->default = $default;
        $this->length = $length;
        $this->charset = $charset;
        $this->collation = $collation;
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
     * Return default value, Note: default value should be string.
     *
     * @return string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Length can be integer value from 0 to 255.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Get collation
     *
     * @return string|null
     */
    public function getCollation(): ?string
    {
        return $this->collation;
    }

    /**
     * Get charset
     *
     * @return string|null
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        $param = [
            'type' => $this->getType(),
            'nullable' => $this->isNullable(),
            'default' => $this->getDefault(),
            'length' => $this->getLength(),
            'comment' => $this->getComment()
        ];
        if ($this->getType() === 'varchar') {
            $param['collation'] = $this->getCollation();
            $param['charset'] = $this->getCharset();
        }
        return $param;
    }
}
