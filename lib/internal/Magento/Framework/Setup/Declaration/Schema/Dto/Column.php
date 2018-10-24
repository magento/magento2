<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Column structural element.
 */
class Column extends GenericElement implements
    ElementInterface,
    TableElementInterface
{
    /**
     * Element type.
     */
    const TYPE = 'column';

    /**
     * @var Table
     */
    private $table;

    /**
     * @var null|string
     */
    private $onCreate;

    /**
     * @var string
     */
    private $comment;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param string $comment
     * @param string|null $onCreate
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        string $comment = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type);
        $this->table = $table;
        $this->onCreate = $onCreate;
        $this->comment = $comment;
    }

    /**
     * Retrieve table name.
     *
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @inheritdoc
     */
    public function getElementType()
    {
        return self::TYPE;
    }

    /**
     * Get On Create statement.
     *
     * @return null|string
     */
    public function getOnCreate()
    {
        return $this->onCreate;
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->comment;
    }
}
