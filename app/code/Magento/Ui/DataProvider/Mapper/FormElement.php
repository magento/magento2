<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
