<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Mapper;

/**
 * Class FormElement
 * @since 2.1.0
 */
class FormElement implements MapperInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    protected $mappings = [];

    /**
     * @param array $mappings
     * @since 2.1.0
     */
    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Retrieve mappings
     *
     * @return array
     * @since 2.1.0
     */
    public function getMappings()
    {
        return $this->mappings;
    }
}
