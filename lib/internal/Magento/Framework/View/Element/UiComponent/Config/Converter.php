<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\View\Layout\Argument\Parser;

/**
 * Class Converter
 * @since 2.0.0
 */
class Converter implements ConverterInterface
{
    /**
     * The key attributes of a node
     */
    const DATA_ATTRIBUTES_KEY = '@attributes';

    /**
     * The key for the data arguments
     */
    const DATA_ARGUMENTS_KEY = '@arguments';

    /**
     * The key of the argument node
     */
    const ARGUMENT_KEY = 'argument';

    /**
     * Key name attribute value
     */
    const NAME_ATTRIBUTE_KEY = 'name';

    /**
     * @var Parser
     * @since 2.0.0
     */
    protected $argumentParser;

    /**
     * Constructor
     *
     * @param Parser $argumentParser
     * @since 2.0.0
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    protected function toArray(\DOMNode $node)
    {
        $result = [];
        $attributes = [];
        // Collect data from attributes
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }
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
                    $arguments = [];
                    for ($i = 0, $iLength = $node->childNodes->length; $i < $iLength; ++$i) {
                        $itemNode = $node->childNodes->item($i);
                        if (empty($itemNode->localName)) {
                            continue;
                        }
                        if ($itemNode->nodeName === static::ARGUMENT_KEY) {
                            $arguments += $this->toArray($itemNode);
                        } else {
                            $result[$itemNode->localName][] = $this->toArray($itemNode);
                        }
                    }
                    if (!empty($arguments)) {
                        $result[static::DATA_ARGUMENTS_KEY] = $arguments;
                    }
                    if (!empty($attributes)) {
                        $result[static::DATA_ATTRIBUTES_KEY] = $attributes;
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * Convert configuration
     *
     * @param \DOMDocument|null $source
     * @return array
     * @since 2.0.0
     */
    public function convert($source)
    {
        if ($source === null) {
            return [];
        }

        return $this->toArray($source);
    }
}
