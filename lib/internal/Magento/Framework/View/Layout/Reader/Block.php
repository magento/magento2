<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\App;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout;

/**
 * Block structure reader
 */
class Block implements Layout\ReaderInterface
{
    /**#@+
     * Supported types
     */
    const TYPE_BLOCK = 'block';
    const TYPE_REFERENCE_BLOCK = 'referenceBlock';
    /**#@-*/

    /**#@+
     * Supported subtypes for blocks
     */
    const TYPE_ARGUMENTS = 'arguments';
    const TYPE_ACTION = 'action';
    /**#@-*/

    /**#@+
     * Names of block attributes in layout
     */
    const ATTRIBUTE_GROUP = 'group';
    const ATTRIBUTE_CLASS = 'class';
    const ATTRIBUTE_TEMPLATE = 'template';
    const ATTRIBUTE_TTL = 'ttl';
    const ATTRIBUTE_DISPLAY = 'display';
    const ATTRIBUTE_ACL = 'acl';
    /**#@-*/

    /**
     * @var array
     */
    protected $attributes = [
        self::ATTRIBUTE_GROUP,
        self::ATTRIBUTE_CLASS,
        self::ATTRIBUTE_TEMPLATE,
        self::ATTRIBUTE_TTL,
        self::ATTRIBUTE_DISPLAY
    ];

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\Argument\Parser
     */
    protected $argumentParser;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool
     */
    protected $readerPool;

    /**
     * @var string
     */
    protected $scopeType;

    /**
     * @var InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\ReaderPool $readerPool
     * @param InterpreterInterface $argumentInterpreter
     * @param string|null $scopeType
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        $scopeType = null
    ) {
        $this->helper = $helper;
        $this->argumentParser = $argumentParser;
        $this->readerPool = $readerPool;
        $this->scopeType = $scopeType;
        $this->argumentInterpreter = $argumentInterpreter;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes()
    {
        return [self::TYPE_BLOCK, self::TYPE_REFERENCE_BLOCK];
    }

    /**
     * {@inheritdoc}
     *
     * @param Context $readerContext
     * @param Layout\Element $currentElement
     * @param Layout\Element $parentElement
     * @return $this
     */
    public function interpret(Context $readerContext, Layout\Element $currentElement)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        switch ($currentElement->getName()) {
            case self::TYPE_BLOCK:
                $this->scheduleBlock($scheduledStructure, $currentElement);
                break;

            case self::TYPE_REFERENCE_BLOCK:
                $this->scheduleReference($scheduledStructure, $currentElement);
                break;

            default:
                break;
        }
        $this->readerPool->interpret($readerContext, $currentElement);
        return $this;
    }

    /**
     * Process block element their attributes and children
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param Layout\Element $currentElement
     * @return void
     */
    protected function scheduleBlock(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $elementName = $this->helper->scheduleStructure(
            $scheduledStructure,
            $currentElement,
            $currentElement->getParent()
        );
        $data = $scheduledStructure->getStructureElementData($elementName, []);
        $data['attributes'] = $this->mergeBlockAttributes($data, $currentElement);
        $this->updateScheduledData($currentElement, $data);
        $this->evaluateArguments($currentElement, $data);
        $scheduledStructure->setStructureElementData($elementName, $data);

        $configPath = (string)$currentElement->getAttribute('ifconfig');
        if (!empty($configPath)) {
            $scheduledStructure->setElementToIfconfigList($elementName, $configPath, $this->scopeType);
        }
    }

    /**
     * Merge Block attributes
     *
     * @param array $elementData
     * @param \Magento\Framework\View\Layout\Element $currentElement
     * @return array
     */
    protected function mergeBlockAttributes(array $elementData, Layout\Element $currentElement)
    {
        if (isset($elementData['attributes'])) {
            $keys = array_keys($elementData['attributes']);
            foreach ($keys as $key) {
                if (isset($currentElement[$key])) {
                    $elementData['attributes'][$key] = (string)$currentElement[$key];
                }
            }
        } else {
            $elementData['attributes'] = [
                self::ATTRIBUTE_CLASS    => (string)$currentElement[self::ATTRIBUTE_CLASS],
                self::ATTRIBUTE_GROUP    => (string)$currentElement[self::ATTRIBUTE_GROUP],
                self::ATTRIBUTE_TEMPLATE => (string)$currentElement[self::ATTRIBUTE_TEMPLATE],
                self::ATTRIBUTE_TTL      => (string)$currentElement[self::ATTRIBUTE_TTL],
                self::ATTRIBUTE_DISPLAY  => (string)$currentElement[self::ATTRIBUTE_DISPLAY],
                self::ATTRIBUTE_ACL  => (string)$currentElement[self::ATTRIBUTE_ACL],
            ];
        }
        return $elementData['attributes'];
    }

    /**
     * Schedule reference data
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param Layout\Element $currentElement
     * @return void
     */
    protected function scheduleReference(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement
    ) {
        $elementName = $currentElement->getAttribute('name');
        $elementRemove = filter_var($currentElement->getAttribute('remove'), FILTER_VALIDATE_BOOLEAN);
        if ($elementRemove) {
            $scheduledStructure->setElementToRemoveList($elementName);
        } else {
            $data = $scheduledStructure->getStructureElementData($elementName, []);
            $data['attributes'] = $this->mergeBlockAttributes($data, $currentElement);
            $this->updateScheduledData($currentElement, $data);
            $this->evaluateArguments($currentElement, $data);
            $scheduledStructure->setStructureElementData($elementName, $data);
        }
    }

    /**
     * Update data for scheduled element
     *
     * @param Layout\Element $currentElement
     * @param array &$data
     * @return array
     */
    protected function updateScheduledData($currentElement, array &$data)
    {
        $actions = $this->getActions($currentElement);
        $arguments = $this->getArguments($currentElement);
        $data['actions'] = isset($data['actions'])
            ? array_merge($data['actions'], $actions)
            : $actions;
        $data['arguments'] = isset($data['arguments'])
            ? array_replace_recursive($data['arguments'], $arguments)
            : $arguments;
        return $data;
    }

    /**
     * Get block attributes
     *
     * @param Layout\Element $blockElement
     * @return array
     */
    protected function getAttributes(Layout\Element $blockElement)
    {
        $attributes = [];
        foreach ($this->attributes as $attributeName) {
            $attributes[$attributeName] = (string)$blockElement->getAttribute($attributeName);
        }
        return $attributes;
    }

    /**
     * Get actions for block element
     *
     * @param Layout\Element $blockElement
     * @return array[]
     */
    protected function getActions(Layout\Element $blockElement)
    {
        $actions = [];
        /** @var $actionElement Layout\Element */
        foreach ($this->getElementsByType($blockElement, self::TYPE_ACTION) as $actionElement) {
            $configPath = $actionElement->getAttribute('ifconfig');
            $methodName = $actionElement->getAttribute('method');
            $actionArguments = $this->parseArguments($actionElement);
            $actions[] = [$methodName, $actionArguments, $configPath, $this->scopeType];
        }
        return $actions;
    }

    /**
     * Get block arguments
     *
     * @param Layout\Element $blockElement
     * @return array
     */
    protected function getArguments(Layout\Element $blockElement)
    {
        $arguments = $this->getElementsByType($blockElement, self::TYPE_ARGUMENTS);
        // We have only one declaration of <arguments> node in block or its reference
        $argumentElement = reset($arguments);
        return $argumentElement ? $this->parseArguments($argumentElement) : [];
    }

    /**
     * Get elements by type
     *
     * @param Layout\Element $element
     * @param string $type
     * @return array
     */
    protected function getElementsByType(Layout\Element $element, $type)
    {
        $elements = [];
        /** @var $childElement Layout\Element */
        foreach ($element as $childElement) {
            if ($childElement->getName() === $type) {
                $elements[] = $childElement;
            }
        }
        return $elements;
    }

    /**
     * Parse argument nodes and return their array representation
     *
     * @param Layout\Element $node
     * @return array
     */
    protected function parseArguments(Layout\Element $node)
    {
        $nodeDom = dom_import_simplexml($node);
        $result = [];
        foreach ($nodeDom->childNodes as $argumentNode) {
            if ($argumentNode instanceof \DOMElement && $argumentNode->nodeName == 'argument') {
                $argumentName = $argumentNode->getAttribute('name');
                $result[$argumentName] = $this->argumentParser->parse($argumentNode);
            }
        }
        return $result;
    }

    /**
     * Compute argument values
     *
     * @param Layout\Element $blockElement
     * @param array $data
     * @return void
     */
    protected function evaluateArguments(Layout\Element $blockElement, array &$data)
    {
        $arguments = $this->getArguments($blockElement);
        foreach ($arguments as $argumentName => $argumentData) {
            if (isset($argumentData['updater'])) {
                continue;
            }
            $result = $this->argumentInterpreter->evaluate($argumentData);
            if (is_array($result)) {
                $data['arguments'][$argumentName] = isset($data['arguments'][$argumentName])
                    ? array_replace_recursive($data['arguments'][$argumentName], $result)
                    : $result;
            } else {
                $data['arguments'][$argumentName] = $result;
            }
        }
    }
}
