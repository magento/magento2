<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Constraint structural element.
 * Used for creating additional rules on db tables.
 */
class Constraint extends GenericElement implements
    ElementInterface,
    TableElementInterface
{
    /**
     * In case if we will need to change this object: add, modify or drop, we will need
     * to define it by its type.
     */
    const TYPE = 'constraint';

    /**
     * Means PRIMARY KEY
     */
    const PRIMARY_TYPE = 'primary';

    /**
     * Means UNIQUE KEY
     */
    const UNIQUE_TYPE = 'unique';

    /**
     * @var Table
     */
    private $table;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table  $table
     */
    public function __construct(
        string $name,
        string $type,
        Table $table
    ) {
        parent::__construct($name, $type);
        $this->table = $table;
    }

    /**
     * Retrieve table object.
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
}
