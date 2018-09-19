<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Reader\Visibility;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Simplexml\Element;
use Magento\Framework\View\Layout\AclCondition;
use Magento\Framework\View\Layout\Argument\Parser;
use Magento\Framework\View\Layout\ConfigCondition;

/**
 * Parse conditions from element.
 */
class Condition
{
    /**
     * Supported subtypes for visibility conditions.
     */
    const TYPE_ARGUMENTS = 'arguments';

    /**
     * @var Parser
     */
    private $argumentParser;

    /**
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    /**
     * @param Parser $argumentParser
     * @param InterpreterInterface $argumentInterpreter
     */
    public function __construct(
        Parser $argumentParser,
        InterpreterInterface $argumentInterpreter
    ) {
        $this->argumentParser = $argumentParser;
        $this->argumentInterpreter = $argumentInterpreter;
    }

    /**
     * @param Element $element
     *
     * @return array
     */
    public function parseConditions(Element $element)
    {
        $visibilityConditions = [];
        $configPath = (string)$element->getAttribute('ifconfig');
        if (!empty($configPath)) {
            $visibilityConditions['ifconfig'] = [
                'name' => ConfigCondition::class,
                'arguments' => [
                    'configPath' => $configPath
                ],
            ];
        }

        $aclResource = (string)$element->getAttribute('aclResource');
        if (!empty($aclResource)) {
            $visibilityConditions['acl'] = [
                'name' => AclCondition::class,
                'arguments' => [
                    'acl' => $aclResource
                ],
            ];
        }

        /** @var $childElement Element */
        foreach ($element as $childElement) {
            if ($childElement->getName() === 'visibilityCondition') {
                $visibilityConditions[$childElement->getAttribute('name')] = [
                    'name' => $childElement->getAttribute('className'),
                    'arguments' => $this->evaluateArguments($childElement),
                ];
            }
        }

        return $visibilityConditions;
    }

    /**
     * Compute argument values
     *
     * @param Element $blockElement
     * @return array
     */
    private function evaluateArguments(Element $blockElement)
    {
        $argumentsData = [];
        $arguments = $this->getArguments($blockElement);
        foreach ($arguments as $argumentName => $argumentData) {
            if (isset($argumentData['updater'])) {
                continue;
            }
            $result = $this->argumentInterpreter->evaluate($argumentData);
            if (is_array($result)) {
                $argumentsData[$argumentName] = isset($argumentsData[$argumentName])
                    ? array_replace_recursive($argumentsData[$argumentName], $result)
                    : $result;
            } else {
                $argumentsData[$argumentName] = $result;
            }
        }

        return $argumentsData;
    }

    /**
     * @param Element $element
     *
     * @return array
     */
    private function getArguments(Element $element)
    {
        $arguments = $this->getElementsByType($element, self::TYPE_ARGUMENTS);
        // We have only one declaration of <arguments> node in block or its reference
        $argumentElement = reset($arguments);
        return $argumentElement ? $this->parseArguments($argumentElement) : [];
    }

    /**
     * Get elements by type
     *
     * @param Element $element
     * @param string $type
     * @return array
     */
    private function getElementsByType(Element $element, $type)
    {
        $elements = [];
        /** @var $childElement Element */
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
     * @param Element $node
     * @return array
     */
    private function parseArguments(Element $node)
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
}
