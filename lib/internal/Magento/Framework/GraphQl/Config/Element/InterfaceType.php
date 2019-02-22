<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

/**
 * Class representing 'interface' GraphQL config element.
 */
class InterfaceType implements TypeInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @var string
     */
    private $typeResolver;

    /**
     * @var string
     */
    private $description;

    /**
     * @param string $name
     * @param string $typeResolver
     * @param Field[] $fields
     * @param string $description
     */
    public function __construct(
        string $name,
        string $typeResolver,
        array $fields,
        string $description
    ) {
        $this->name = $name;
        $this->fields = $fields;
        $this->typeResolver = $typeResolver;
        $this->description = $description;
    }

    /**
     * Get the type name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get a list of fields that make up the possible return or input values of a type.
     *
     * @return Field[]
     */
    public function getFields() : array
    {
        return $this->fields;
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

    /**
     * Get a human-readable description of the type.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
