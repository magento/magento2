<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Generic element DTO.
 *
 * Data transfer object, that provides access to basic attributes of various structural elements.
 *
 * Under structural element means one of next element, with can be represented in db schema :
 *  - column
 *  - constraint
 *  - index
 */
abstract class GenericElement implements
    ElementInterface
{
    /**
     * High level type.
     *
     * @var string
     */
    private $type;

    /**
     * Element name.
     *
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }
}
