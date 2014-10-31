<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\App;

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

    /**
     * @var array
     */
    protected $attributes = ['group', 'class', 'template', 'ttl'];

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\Argument\Parser
     */
    protected $argumentParser;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Pool
     */
    protected $readerPool;

    /**
     * @var string
     */
    protected $scopeType;

    /**
     * Constructor
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\Reader\Pool $readerPool
     * @param App\Config\ScopeConfigInterface $scopeConfig
     * @param App\ScopeResolverInterface $scopeResolver
     * @param string|null $scopeType
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\Reader\Pool $readerPool,
        App\Config\ScopeConfigInterface $scopeConfig,
        App\ScopeResolverInterface $scopeResolver,
        $scopeType = null
    ) {
        $this->helper = $helper;
        $this->argumentParser = $argumentParser;
        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
        $this->readerPool = $readerPool;
        $this->scopeType = $scopeType;
    }

    /**
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
    public function process(Context $readerContext, Layout\Element $currentElement, Layout\Element $parentElement)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        switch ($currentElement->getName()) {
            case self::TYPE_BLOCK:
                $this->scheduleBlock($scheduledStructure, $currentElement, $parentElement);
                break;

            case self::TYPE_REFERENCE_BLOCK:
                $this->scheduleReference($scheduledStructure, $currentElement);
                break;

            default:
                break;
        }
        return $this->readerPool->readStructure($readerContext, $currentElement);
    }

    /**
     * Process block element their attributes and children
     *
     * @param Layout\ScheduledStructure $scheduledStructure
     * @param Layout\Element $currentElement
     * @param Layout\Element $parentElement
     * @return void
     */
    protected function scheduleBlock(
        Layout\ScheduledStructure $scheduledStructure,
        Layout\Element $currentElement,
        Layout\Element $parentElement
    ) {
        $elementName = $this->helper->scheduleStructure($scheduledStructure, $currentElement, $parentElement);
        $data = $scheduledStructure->getStructureElementData($elementName, []);
        $data['attributes'] = $this->getAttributes($currentElement);
        $this->updateScheduledData($currentElement, $data);
        $scheduledStructure->setStructureElementData($elementName, $data);

        $configPath = (string)$currentElement->getAttribute('ifconfig');
        if (!empty($configPath)
            && !$this->scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
        ) {
            $scheduledStructure->setElementToRemoveList($elementName);
        }
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
        $data = $scheduledStructure->getStructureElementData($elementName, []);
        $this->updateScheduledData($currentElement, $data);
        $scheduledStructure->setStructureElementData($elementName, $data);
    }

    /**
     * Update data for scheduled element
     *
     * @param Layout\Element $currentElement
     * @param array $data
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
            if ($configPath
                && !$this->scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
            ) {
                continue;
            }
            $methodName = $actionElement->getAttribute('method');
            $actionArguments = $this->_parseArguments($actionElement);
            $actions[] = [$methodName, $actionArguments];
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
        // We have only one declaration of <arguments> node in block or it's reference
        $argumentElement = reset($arguments);
        return $argumentElement ? $this->_parseArguments($argumentElement) : [];
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
    protected function _parseArguments(Layout\Element $node)
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
