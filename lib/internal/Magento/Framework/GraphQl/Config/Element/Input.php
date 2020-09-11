<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

/**
 * Class representing 'input' GraphQL config element.
 */
class Input implements TypeInterface
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
    private $description;

    /**
     * @var array
     */
    private $deprecated;

    /**
     * @param string $name
     * @param Field[] $fields
     * @param string $description
     * @param array $deprecated
     */
    public function __construct(
        string $name,
        array $fields,
        string $description,
        array $deprecated = []
    ) {
        $this->name = $name;
        $this->fields = $fields;
        $this->description = $description;
        $this->deprecated = $deprecated;
    }

    /**
     * Get the type name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get a list of fields that make up the possible return or input values of a type.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get a human-readable description of the type.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Return the deprecated annotation for the input
     *
     * @return array
     */
    public function getDeprecated() : array
    {
        return $this->deprecated;
    }
}
