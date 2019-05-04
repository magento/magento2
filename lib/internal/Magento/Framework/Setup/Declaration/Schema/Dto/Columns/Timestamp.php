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
 * Timestamp column.
 * Declared in SQL, like Timestamp.
 * Has 2 additional params: default and on_update.
 */
class Timestamp extends Column implements
    ElementDiffAwareInterface,
    ColumnDefaultAwareInterface,
    ColumnNullableAwareInterface
{
    /**
     * @var string
     */
    private $default;

    /**
     * @var null|string
     */
    private $onUpdate;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param string $default
     * @param bool $nullable
     * @param string|null $onUpdate
     * @param string|null $comment
     * @param string|null $onCreate
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        string $default,
        bool $nullable = true,
        string $onUpdate = null,
        string $comment = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type, $table, $comment, $onCreate);
        $this->default = $default;
        $this->onUpdate = $onUpdate;
        $this->nullable = $nullable;
    }

    /**
     * Return default value.
     *
     * @return int|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Retrieve on_update param.
     *
     * @return string
     */
    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'default' => $this->getDefault(),
            'onUpdate' => $this->getOnUpdate(),
            'comment' => $this->getComment()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
