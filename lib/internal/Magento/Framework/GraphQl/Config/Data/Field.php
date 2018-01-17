<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Structured data object of a GraphQL field. Fields are used to describe possible values for a type/interface.
 */
class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var string
     */
    private $itemType;

    /**
     * @var string
     */
    private $resolver;
    /**
     * @var string
     */
    private $description;

    /**
     * @param string $name
     * @param string $type
     * @param bool $required
     * @param string|null $itemType
     * @param string|null $resolver
     * @param string|null $description
     * @param array $arguments
     */
    public function __construct(
        string $name,
        string $type,
        bool $required,
        string $itemType = "",
        string $resolver = "",
        string $description = "",
        array $arguments = []
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->itemType = $itemType;
        $this->resolver = $resolver;
        $this->description = $description;
        $this->arguments = $arguments;
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the type's configured name.
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Get the item type of if the field is a list of items. Returns empty string otherwise.
     *
     * @return string
     */
    public function getItemType() : string
    {
        return $this->itemType;
    }

    /**
     * Return true if field is a list of items. False otherwise.
     *
     * @return bool
     */
    public function isList() : bool
    {
        return !empty($this->itemType);
    }

    /**
     * Return true if the field is required by an input type to be populated. False otherwise.
     *
     * @return bool
     */
    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * Get the resolver for a given field. If no resolver is specified, return an empty string.
     *
     * @return string
     */
    public function getResolver() : string
    {
        return $this->resolver;
    }

    /**
     * Get the list of arguments configured for the field. Return an empty array if no arguments are configured.
     *
     * @return Argument[]
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }

    /**
     * Return the human-readable description of the field.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
