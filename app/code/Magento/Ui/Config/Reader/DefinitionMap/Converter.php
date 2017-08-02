<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader\DefinitionMap;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\View\Layout\Argument\Parser;

/**
 * Converter for definition.map.xml
 * @since 2.2.0
 */
class Converter implements ConverterInterface
{
    /**
     * The key of the argument node
     */
    const ARGUMENT_KEY = 'argument';

    /**
     * The key of the include component
     */
    const INCLUDE_KEY = 'include';

    /**
     * The array key sub components
     */
    const CURRENT_SCHEMA_KEY = 'current';

    /**
     * Key name attribute value
     */
    const NAME_ATTRIBUTE_KEY = 'name';

    /**
     * @var Parser
     * @since 2.2.0
     */
    private $argumentParser;

    /**
     * Converter constructor.
     *
     * @param Parser $argumentParser
     * @since 2.2.0
     */
    public function __construct(Parser $argumentParser)
    {
        $this->argumentParser = $argumentParser;
    }

    /**
     * Transform Xml to array
     *
     * @param \DOMNode $node
     * @return array|string
     * @since 2.2.0
     */
    private function toArray(\DOMNode $node)
    {
        $result = [];
        $attributes = [];
        // Collect data from attributes
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }
        }

        if (isset($attributes[static::INCLUDE_KEY])) {
            $result[static::INCLUDE_KEY] = $attributes[static::INCLUDE_KEY];
            unset($attributes[static::INCLUDE_KEY]);
        }

        switch ($node->nodeType) {
            case XML_TEXT_NODE:
            case XML_COMMENT_NODE:
            case XML_CDATA_SECTION_NODE:
                break;
            default:
                if ($node->localName === static::ARGUMENT_KEY) {
                    if (!isset($attributes[static::NAME_ATTRIBUTE_KEY])) {
                        throw new \InvalidArgumentException(
                            'Attribute "' . static::NAME_ATTRIBUTE_KEY . '" is absent in the attributes node.'
                        );
                    }
                    $result[ $attributes[static::NAME_ATTRIBUTE_KEY] ] = $this->argumentParser->parse($node);
                } else {
                    list($arguments, $childResult) = $this->convertChildNodes($node);

                    $result += $this->processArguments($arguments);
                    $result += $childResult;
                }
                break;
        }

        return $result;
    }

    /**
     * Retrieve component name
     *
     * @param \DOMNode $node
     * @return string
     * @since 2.2.0
     */
    private function getComponentName(\DOMNode $node)
    {
        $result = $node->localName;
        if (!$node->hasAttributes()) {
            return $result;
        }
        foreach ($node->attributes as $attribute) {
            if ($attribute->name == static::NAME_ATTRIBUTE_KEY) {
                $result = $attribute->value;
                break;
            }
        }

        return $result;
    }

    /**
     * Convert configuration
     *
     * @param \DOMDocument|null $source
     * @return array
     * @since 2.2.0
     */
    public function convert($source)
    {
        if ($source === null) {
            return [];
        }

        $result = $this->toArray($source);
        $result = empty($result) ? $result : reset($result);
        $schemaMap = [];
        foreach ($result as $componentName => $componentData) {
            $schemaMap[$componentName] = $this->processMap($componentData, $result);
        }
        return $schemaMap;
    }

    /**
     * Process include directives and return current schema
     *
     * @param array $componentData
     * @param array $sourceMap
     * @return array
     * @since 2.2.0
     */
    private function processMap($componentData, $sourceMap)
    {
        $result = [];
        if (isset($componentData[static::INCLUDE_KEY])) {
            if (isset($sourceMap[$componentData[static::INCLUDE_KEY]])) {
                $result = array_replace_recursive(
                    $this->processMap($sourceMap[$componentData[static::INCLUDE_KEY]], $sourceMap),
                    $result
                );
            }
        }

        if (isset($componentData[static::CURRENT_SCHEMA_KEY])) {
            $result = array_replace_recursive($componentData[static::CURRENT_SCHEMA_KEY], $result);
        }

        return $result;
    }

    /**
     * Convert child nodes of $node
     *
     * @param \DOMNode $node
     * @return array
     * @since 2.2.0
     */
    private function convertChildNodes(\DOMNode $node)
    {
        $arguments = [];
        $childResult = [];
        foreach ($node->childNodes as $itemNode) {
            if (empty($itemNode->localName)) {
                continue;
            }
            if ($itemNode->localName === static::ARGUMENT_KEY) {
                $arguments += $this->toArray($itemNode);
            } else {
                $childResult[$this->getComponentName($itemNode)] = $this->toArray($itemNode);
            }
        }

        return [$arguments, $childResult];
    }

    /**
     * Process component arguments
     *
     * @param array $arguments
     * @return array
     * @since 2.2.0
     */
    private function processArguments(array $arguments)
    {
        $result = [];
        if (!empty($arguments)) {
            $result[static::ARGUMENT_KEY] = $arguments;
        }

        return $result;
    }
}
