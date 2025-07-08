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
 * Text column.
 * Declared in SQL, like: TEXT, TINYTEXT, MEDIUMTEXT, LONGTEXT.
 */
class Text extends Column implements
    ElementDiffAwareInterface,
    ColumnNullableAwareInterface
{
    /**
     * @var bool
     */
    private $nullable;

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
     * @param bool $nullable
     * @param string|null $comment
     * @param string|null $onCreate
     * @param string|null $charset
     * @param string|null $collation
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        bool $nullable = true,
        ?string $comment = null,
        ?string $onCreate = null,
        ?string $charset = 'utf8mb4',
        ?string $collation = 'utf8mb4_general_ci'
    ) {
        parent::__construct($name, $type, $table, $comment, $onCreate);
        $this->nullable = $nullable;
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
        return [
            'type' => $this->getType(),
            'nullable' => $this->isNullable(),
            'comment' => $this->getComment(),
            'collation' => $this->getCollation(),
            'charset' => $this->getCharset()
        ];
    }
}
