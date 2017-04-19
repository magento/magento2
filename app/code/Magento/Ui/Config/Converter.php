<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

use Magento\Framework\Config\ConverterInterface as ConfigConverterInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\View\Layout\Argument\Parser;
use Magento\Ui\Config\Argument\ParserInterface;

/**
 * Converter for UI Component instances configuration files
 */
class Converter implements ConfigConverterInterface
{
    /**
     * The key attributes of a node
     */
    const DATA_ATTRIBUTES_KEY = 'attributes';

    /**
     * The key for the data arguments
     */
    const DATA_ARGUMENTS_KEY = 'arguments';

    /**
     * The key of sub components
     */
    const DATA_COMPONENTS_KEY = 'children';

    /**
     * The key of the arguments node
     */
    const ARGUMENT_KEY = 'argument';

    /**
     * The key of the settings component
     */
    const SETTINGS_KEY = 'settings';

    /**
     * Key name attribute value
     */
    const NAME_ATTRIBUTE_KEY = 'name';

    /**
     * Key class attribute value
     */
    const CLASS_ATTRIBUTE_KEY = 'class';

    /**
     * @var Parser
     */
    private $argumentParser;

    /**
     * @var array
     */
    private $schemaMap = [];

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var ConverterUtils
     */
    private $converterUtils;

    /**
     * @param Parser $argumentParser
     * @param ParserInterface $parser
     * @param ReaderInterface $reader
     * @param ConverterUtils $converterUtils
     */
    public function __construct(
        Parser $argumentParser,
        ParserInterface $parser,
        ReaderInterface $reader,
        ConverterUtils $converterUtils
    ) {
        $this->argumentParser = $argumentParser;
        $this->reader = $reader;
        $this->parser = $parser;
        $this->converterUtils = $converterUtils;
    }

    /**
     * Convert nodes and child nodes to array
     *
     * @param \DOMNode $node
     * @return array|string
     */
    private function toArray(\DOMNode $node)
    {
        $result = [];
        $attributes = [];
        // Collect data from attributes
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                if ($attribute->name == 'noNamespaceSchemaLocation') {
                    continue;
                }
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
                    $result[$attributes[static::NAME_ATTRIBUTE_KEY]] = $this->argumentParser->parse($node);
                } else {
                    $resultComponent = $this->convertNode($node);
                    list($arguments, $childResult) = $this->convertChildNodes($node);

                    $result = array_merge(
                        $this->processArguments($arguments, $resultComponent),
                        $this->processAttributes($attributes),
                        $this->processChildResult($node, $childResult)
                    );
                }

                break;
        }

        return $result;
    }

    /**
     * Convert configuration to array
     *
     * @param \DOMDocument|null $source
     * @return array
     */
    public function convert($source)
    {
        if ($source === null) {
            return [];
        }

        if (!$this->schemaMap) {
            $this->schemaMap = $this->reader->read();
        }
        $result = $this->toArray($source);
        return empty($result) ? $result : reset($result);
    }

    /**
     * Convert and parse node to array according to definition.map.xml
     *
     * @param \DOMNode $node
     * @return array
     */
    private function convertNode(\DOMNode $node)
    {
        $resultComponent = [];
        if (empty($node->localName)
            || !$this->converterUtils->isUiComponent($node)
            || !isset($this->schemaMap[$node->localName])
        ) {
            return $resultComponent;
        }

        foreach ($this->schemaMap[$node->localName] as $componentData) {
            $result = [];
            foreach ($componentData as $dataKey => $dataValue) {
                $resultParser = $this->parser->parse($dataValue, $node);
                if ($resultParser) {
                    $result[$dataKey] = $resultParser;
                }
            }
            $resultComponent = array_replace_recursive($resultComponent, $result);
        }

        return $resultComponent;
    }

    /**
     * Process component arguments
     *
     * @param array $arguments
     * @param array $resultComponent
     * @return array
     */
    private function processArguments(array $arguments, array $resultComponent)
    {
        $result = [];
        if (!empty($arguments) || !empty($resultComponent)) {
            $result[static::DATA_ARGUMENTS_KEY] = array_replace_recursive($resultComponent, $arguments);
        }
        return $result;
    }

    /**
     * Process component attributes
     *
     * @param array $attributes
     * @return array
     */
    private function processAttributes(array $attributes)
    {
        $result = [];
        if (!empty($attributes)) {
            $result[static::DATA_ATTRIBUTES_KEY] = $attributes;
        }
        return $result;
    }

    /**
     * @param \DOMNode $node
     * @param array $childResult
     * @return array
     */
    private function processChildResult(\DOMNode $node, array $childResult)
    {
        $result = [];
        if ($node->parentNode !== null) {
            $result[static::DATA_COMPONENTS_KEY] = $childResult;
        } else {
            $result = $childResult;
        }

        return $result;
    }

    /**
     * Convert child nodes of $node
     *
     * @param \DOMNode $node
     * @return array
     */
    private function convertChildNodes(\DOMNode $node)
    {
        $arguments = [];
        $childResult = [];
        for ($i = 0, $iLength = $node->childNodes->length; $i < $iLength; ++$i) {
            $itemNode = $node->childNodes->item($i);
            if ($itemNode->localName == null) {
                continue;
            }
            if ($itemNode->localName === static::ARGUMENT_KEY) {
                $arguments += $this->toArray($itemNode);
            } elseif (
                $this->converterUtils->isUiComponent($itemNode)
                && isset($this->schemaMap[$itemNode->localName])
            ) {
                $itemNodeName = $this->converterUtils->getComponentName($itemNode);
                $childResult[$itemNodeName] = $this->toArray($itemNode);
                // 'uiComponentType' is needed this for Reader to merge default values from definition
                $childResult[$itemNodeName]['uiComponentType'] = $itemNode->localName;
            } else {
                continue;
            }
        }

        return [$arguments, $childResult];
    }
}
