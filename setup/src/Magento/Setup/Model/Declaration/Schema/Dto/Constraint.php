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
class Constraint extends GenericElement implements
    ElementInterface,
    TableElementInterface
{
    /**
     * @inheritdoc
     */
    protected $elementType = 'constraint';

    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * @param array $structuralElementData
     * @param $elementType
     */
    public function __construct(array $structuralElementData, $elementType)
    {
        $this->structuralElementData = $structuralElementData;
        $this->elementType = $elementType;
    }

    /**
     * Retrieve table name
     *
     * @return Table
     */
    public function getTable()
    {
        return $this->structuralElementData['table'];
    }
}
