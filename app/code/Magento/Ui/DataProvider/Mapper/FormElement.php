<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\DataProvider\Mapper;

/**
 * Class FormElement
 */
class FormElement implements MapperInterface
{
    /**
     * @var array
     */
    protected $mappings = [];

    /**
     * @param array $mappings
     */
    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Retrieve mappings
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->mappings;
    }
}
