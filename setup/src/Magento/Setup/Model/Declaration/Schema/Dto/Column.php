<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * Constraint structural element
 * Used for creating additional rules on db tables
 */
class Column extends GenericElement implements
    ElementInterface,
    TableElementInterface
{
    /**
     * In case if we will need to change this object: add, modify or drop, we will need
     * to define it by its type
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
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param string|null $onCreate
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        string $onCreate = null
    ) {
        parent::__construct($name, $type);
        $this->table = $table;
        $this->onCreate = $onCreate;
    }

    /**
     * Retrieve table name
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
     * @return null|string
     */
    public function getOnCreate()
    {
        return $this->onCreate;
    }
}
