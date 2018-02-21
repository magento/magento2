<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Block;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Swagger\Api\Block\SchemaTypesInterface;
use Magento\Swagger\Api\SchemaTypeInterface;

/**
 * Schema Type Options.
 */
class SchemaTypes implements SchemaTypesInterface, ArgumentInterface
{
    /**
     * @var SchemaTypeInterface
     */
    private $default;
    /**
     * @var SchemaTypeInterface[]|null
     */
    private $types;

    /**
     * SchemaTypes constructor.
     *
     * @param array|SchemaTypeInterface[] $types
     */
    public function __construct(
        array $types = []
    ) {
        $this->types = $types;
        if (count($this->types) > 0) {
            $this->default = array_values($this->types)[0];
        }
    }

    /**
     * Retrieve the available types of Swagger schema.
     *
     * @return SchemaTypeInterface[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Retrieve the default schema type for Swagger.
     *
     * @return SchemaTypeInterface|null
     */
    public function getDefault()
    {
        return $this->default;
    }
}
