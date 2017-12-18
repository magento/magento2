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
    ElementInterface
{
    /**
     * @var string
     */
    private $elementType;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param string $elementType
     */
    public function __construct(string $name, string $elementType)
    {
        $this->elementType = $elementType;
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getElementType()
    {
        return $this->elementType;
    }
}
