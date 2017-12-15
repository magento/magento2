<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This is data transfer object, that provides access to basic attributes of different
 * structural elements
 *
 * Under structural element means one of next element, with which db schema can be represented:
 *  - column
 *  - constraint
 *  - index
 */
abstract class GenericElement implements
    ElementInterface,
    ElementRenamedInterface
{
    /**
     * Data that comes from reader and consist all information
     * about structural element
     *
     * @var array
     */
    protected $structuralElementData = [];

    /**
     * Structural element type
     *
     * @var string
     */
    protected $elementType;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->structuralElementData['name'];
    }

    /**
     * @inheritdoc
     */
    public function getResource()
    {
        return $this->structuralElementData['resource'];
    }

    /**
     * @inheritdoc
     */
    public function wasRenamedFrom()
    {
        return $this->structuralElementData['wasRenamedFrom'];
    }

    /**
     * @inheritdoc
     */
    public function getElementType()
    {
        return $this->elementType;
    }
}
