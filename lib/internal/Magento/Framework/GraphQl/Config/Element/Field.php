<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

/**
 * Class representing 'field' GraphQL config element.
 *
 * Fields are used to describe possible values for a type/interface.
 *
 * @api
 */
class Field implements OutputFieldInterface
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
     * @var bool
     */
    private $isList;

    /**
     * @var string
     */
    private $resolver;
    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $cache;

    /**
     * @var array
     */
    private $deprecated;

    /**
     * @var array
     */
    private $directives;

    /**
     * @param string $name
     * @param string $type
     * @param bool $required
     * @param bool $isList
     * @param string $itemType
     * @param string $resolver
     * @param string $description
     * @param array $arguments
     * @param array $cache
     * @param array $deprecated
     * @param array $directives
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $type,
        bool $required,
        bool $isList,
        string $itemType = '',
        string $resolver = '',
        string $description = '',
        array $arguments = [],
        array $cache = [],
        array $deprecated = [],
        array $directives = []
    ) {
        $this->name = $name;
        $this->type = $isList ? $itemType : $type;
        $this->required = $required;
        $this->isList = $isList;
        $this->resolver = $resolver;
        $this->description = $description;
        $this->arguments = $arguments;
        $this->cache = $cache;
        $this->deprecated = $deprecated;
        $this->directives = $directives;
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
    public function getTypeName() : string
    {
        return $this->type;
    }

    /**
     * Return true if field is a list of items. False otherwise.
     *
     * @return bool
     */
    public function isList() : bool
    {
        return $this->isList;
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
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Return the cache tag for the field.
     *
     * @return array|null
     */
    public function getCache() : array
    {
        return $this->cache;
    }

    /**
     * Return the deprecated annotation for the field
     *
     * @return array
     */
    public function getDeprecated() : array
    {
        return $this->deprecated;
    }

    /**
     * Return the custom directives of the field, read by the directive readers of FieldMetaReader.
     *
     * Format: [$readerName => $readerResult, ...]. Only the directives present on the field are returned.
     *
     * @return array
     */
    public function getDirectives() : array
    {
        return $this->directives;
    }
}
