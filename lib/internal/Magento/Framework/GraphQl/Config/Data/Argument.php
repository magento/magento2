<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Data;

/**
 * Structured data object for arguments of a GraphQL type.
 *
 * Arguments of a type in GraphQL are used to gather client input to affect how a query will return data.
 */
class Argument
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
     * @var string
     */
    private $baseType;

    /**
     * @var string
     */
    private $itemType;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var bool
     */
    private $itemsRequired;

    /**
     * @param string $name
     * @param string $type
     * @param string $baseType
     * @param string $itemType
     * @param string $description
     * @param bool $required
     * @param bool $itemsRequired
     */
    public function __construct(
        string $name,
        string $type,
        string $baseType,
        string $itemType,
        string $description,
        bool $required,
        bool $itemsRequired = false
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->baseType = $baseType;
        $this->itemType = $itemType;
        $this->description = $description;
        $this->required = $required;
        $this->itemsRequired = $itemsRequired;
    }

    /**
     * Get the name of the argument.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the argument type's configured name
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Get the argument's base type. This can be used to inherit fields for a filter or sort input, etc.
     *
     * @return string
     */
    public function getBaseType() : string
    {
        return $this->baseType;
    }

    /**
     * Get the item type if the argument is a list of input items.
     *
     * @return string
     */
    public function getItemType() : string
    {
        return $this->itemType;
    }

    /**
     * Return true if argument is a list of input items, otherwise false if it is a single object/scalar.
     *
     * @return bool
     */
    public function isList() : bool
    {
        return !empty($this->itemType);
    }

    /**
     * Return true if argument is required when invoking the query where the argument is specified. False otherwise.
     *
     * @return bool
     */
    public function isRequired() : bool
    {
        return $this->required;
    }

    /**
     * Return true if item is a list, and if that list must be populated by at least one item. False otherwise.
     *
     * @return bool
     */
    public function areItemsRequired() : bool
    {
        return $this->itemsRequired;
    }

    /**
     * Return the human-readable description of the argument containing it's documentation.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
}
