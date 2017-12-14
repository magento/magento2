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
     * @inheritdoc
     */
    protected $elementType = 'column';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * @param array $structuralElementData
     * @param string $elementType
     */
    public function __construct(array $structuralElementData, $elementType)
    {
        $this->structuralElementData = $structuralElementData;
        $this->elementType = $elementType;
    }

    /**
     * Retrieve table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->structuralElementData['table'];
    }
}
