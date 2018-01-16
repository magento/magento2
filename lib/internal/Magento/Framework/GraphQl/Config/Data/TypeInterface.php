<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Describes the configured data for a GraphQL interface type.
 */
class TypeInterface extends Type implements StructureInterface
{
    /**
     * @var string
     */
    private $typeResolver;

    /**
     * @param string $name
     * @param string $typeResolver
     * @param array $fields
     * @param string $description
     */
    public function __construct(
        string $name,
        string $typeResolver,
        array $fields,
        string $description = ""
    ) {
        parent::__construct($name, $fields, [], $description);
        $this->typeResolver = $typeResolver;
    }

    /**
     * Return the name of the resolver class that determines the concrete type to display in the result.
     *
     * @return string
     */
    public function getTypeResolver()
    {
        return $this->typeResolver;
    }
}
