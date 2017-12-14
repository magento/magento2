<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * Index structural element
 * Used to speedup read operations from SQL database
 */
class Index extends GenericElement implements
    ElementInterface,
    TableElementInterface,
    ElementDiffAwareInterface
{
    /**
     * @inheritdoc
     */
    protected $elementType = 'index';

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
     * Return columns in order, in which they should go in composite index
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->structuralElementData['column'];
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

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->elementType,
            'columns' => array_map(
                function (Column $column) {
                    return $column->getName();
                },
                $this->getColumns()
            )
        ];
    }
}
