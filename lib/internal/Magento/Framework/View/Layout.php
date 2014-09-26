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
namespace Magento\Framework\View;

use Magento\Framework\View\Layout\Element;

/**
 * Layout model
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Layout extends \Magento\Framework\Simplexml\Config implements \Magento\Framework\View\LayoutInterface
{
    /**
     * Scheduled structure array index for name
     */
    const SCHEDULED_STRUCTURE_INDEX_NAME = 0;

    /**
     * Scheduled structure array index for alias
     */
    const SCHEDULED_STRUCTURE_INDEX_ALIAS = 1;

    /**
     * Scheduled structure array index for parent element name
     */
    const SCHEDULED_STRUCTURE_INDEX_PARENT_NAME = 2;

    /**
     * Scheduled structure array index for sibling element name
     */
    const SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME = 3;

    /**
     * Scheduled structure array index for is after parameter
     */
    const SCHEDULED_STRUCTURE_INDEX_IS_AFTER = 4;

    /**
     * Scheduled structure array index for layout element object
     */
    const SCHEDULED_STRUCTURE_INDEX_LAYOUT_ELEMENT = 5;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * Layout Update module
     *
     * @var \Magento\Framework\View\Layout\ProcessorInterface
     */
    protected $_update;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $_uiComponentFactory;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * Blocks registry
     *
     * @var array
     */
    protected $_blocks = array();

    /**
     * Cache of elements to output during rendering
     *
     * @var array
     */
    protected $_output = array();

    /**
     * Helper blocks cache for this layout
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * A variable for transporting output into observer during rendering
     *
     * @var \Magento\Framework\Object
     */
    protected $_renderingOutput;

    /**
     * Cache of generated elements' HTML
     *
     * @var array
     */
    protected $_renderElementCache = array();

    /**
     * Layout structure model
     *
     * @var \Magento\Framework\Data\Structure
     */
    protected $_structure;

    /**
     * An increment to generate names
     *
     * @var int
     */
    protected $_nameIncrement = array();

    /**
     * @var \Magento\Framework\View\Layout\Argument\Parser
     */
    protected $argumentParser;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure
     */
    protected $_scheduledStructure;

    /**
     * Renderers registered for particular name
     *
     * @var array
     */
    protected $_renderers = array();

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Application configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Logger $logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     */
    protected $_processorFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var bool
     */
    protected $isPrivate = false;

    /**
     * @var string
     */
    protected $scopeType;

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface
     */
    protected $themeResolver;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var bool
     */
    protected $cacheable;

    /**
     * @var \Magento\Framework\View\Page\Config\Reader
     */
    protected $pageConfigReader;

    /**
     * @var \Magento\Framework\View\Page\Config\Generator
     */
    protected $pageConfigGenerator;

    /**
     * @param \Magento\Framework\View\Layout\ProcessorFactory $processorFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiElementFactory,
     * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
     * @param \Magento\Framework\Data\Structure $structure
     * @param \Magento\Framework\View\Layout\Argument\Parser $argumentParser
     * @param \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter
     * @param \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param Page\Config\Reader $pageConfigReader
     * @param Page\Config\Generator $pageConfigGenerator
     * @param string $scopeType
     * @param bool $cacheable
     */
    public function __construct(
        \Magento\Framework\View\Layout\ProcessorFactory $processorFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Data\Structure $structure,
        \Magento\Framework\View\Layout\Argument\Parser $argumentParser,
        \Magento\Framework\Data\Argument\InterpreterInterface $argumentInterpreter,
        \Magento\Framework\View\Layout\ScheduledStructure $scheduledStructure,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\View\Page\Config\Reader $pageConfigReader,
        \Magento\Framework\View\Page\Config\Generator $pageConfigGenerator,
        $scopeType,
        $cacheable = true
    ) {
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_uiComponentFactory = $uiComponentFactory;
        $this->_uiComponentFactory->setLayout($this);
        $this->_blockFactory = $blockFactory;
        $this->_appState = $appState;
        $this->_structure = $structure;
        $this->argumentParser = $argumentParser;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->_elementClass = 'Magento\Framework\View\Layout\Element';
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        $this->_renderingOutput = new \Magento\Framework\Object;
        $this->_scheduledStructure = $scheduledStructure;
        $this->_processorFactory = $processorFactory;
        $this->_logger = $logger;
        $this->messageManager = $messageManager;
        $this->scopeType = $scopeType;
        $this->themeResolver = $themeResolver;
        $this->scopeResolver = $scopeResolver;
        $this->cacheable = $cacheable;
        $this->pageConfigReader = $pageConfigReader;
        $this->pageConfigGenerator = $pageConfigGenerator;
    }

    /**
     * Cleanup circular references between layout & blocks
     *
     * Destructor should be called explicitly in order to work around the PHP bug
     * https://bugs.php.net/bug.php?id=62468
     */
    public function __destruct()
    {
        if (isset($this->_update) && is_object($this->_update)) {
            $this->_update->__destruct();
            $this->_update = null;
        }
        $this->_blocks = array();
        $this->_xml = null;
    }

    /**
     * Retrieve the layout update instance
     *
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    public function getUpdate()
    {
        if (!$this->_update) {
            $theme = $this->themeResolver->get();
            $this->_update = $this->_processorFactory->create(array('theme' => $theme));
        }
        return $this->_update;
    }

    /**
     * Layout xml generation
     *
     * @return $this
     */
    public function generateXml()
    {
        $xml = $this->getUpdate()->asSimplexml();
        $this->setXml($xml);
        $this->_structure->importElements(array());
        return $this;
    }

    /**
     * Create structure of elements from the loaded XML configuration
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function generateElements()
    {
        \Magento\Framework\Profiler::start(__CLASS__ . '::' . __METHOD__);
        \Magento\Framework\Profiler::start('build_structure');

        $this->_scheduledStructure->flushScheduledStructure();

        $this->_readStructure($this->getNode());
        $this->_addToOutputRootContainers($this->getNode());

        while (false === $this->_scheduledStructure->isStructureEmpty()) {
            $this->_scheduleElement(key($this->_scheduledStructure->getStructure()));
        }
        $this->_scheduledStructure->flushPaths();

        foreach ($this->_scheduledStructure->getListToMove() as $elementToMove) {
            $this->_moveElementInStructure($elementToMove);
        }

        foreach ($this->_scheduledStructure->getListToRemove() as $elementToRemove) {
            $this->_removeElement($elementToRemove);
        }

        \Magento\Framework\Profiler::stop('build_structure');

        \Magento\Framework\Profiler::start('generate_elements');

        $this->pageConfigGenerator->process();

        while (false === $this->_scheduledStructure->isElementsEmpty()) {
            list($type, $node, $actions, $args, $attributes) = current($this->_scheduledStructure->getElements());
            $elementName = key($this->_scheduledStructure->getElements());

            if ($type == Element::TYPE_UI_COMPONENT) {
                $this->_generateUiComponent($elementName);
            } else if ($type == Element::TYPE_BLOCK) {
                $this->_generateBlock($elementName);
            } else {
                $this->_generateContainer($elementName, (string)$node[Element::CONTAINER_OPT_LABEL], $attributes);
                $this->_scheduledStructure->unsetElement($elementName);
            }
        }
        \Magento\Framework\Profiler::stop('generate_elements');
        \Magento\Framework\Profiler::stop(__CLASS__ . '::' . __METHOD__);
    }

    /**
     * Add parent containers to output
     *
     * @param Element $nodeList
     * @return $this
     */
    protected function _addToOutputRootContainers(Element $nodeList)
    {
        /** @var $node Element */
        foreach ($nodeList as $node) {
            if ($node->getName() === Element::TYPE_CONTAINER) {
                $this->addOutputElement($node->getElementName());
            }
        }
        return $this;
    }

    /**
     * Remove scheduled element
     *
     * @param string $elementName
     * @param bool $isChild
     * @return $this
     */
    protected function _removeElement($elementName, $isChild = false)
    {
        $elementsToRemove = array_keys($this->_structure->getChildren($elementName));
        $this->_scheduledStructure->unsetElement($elementName);

        foreach ($elementsToRemove as $element) {
            $this->_removeElement($element, true);
        }

        if (!$isChild) {
            $this->_structure->unsetElement($elementName);
            $this->_scheduledStructure->unsetElementFromListToRemove($elementName);
        }
        return $this;
    }

    /**
     * Move element in scheduled structure
     *
     * @param string $element
     * @return $this
     */
    protected function _moveElementInStructure($element)
    {
        list($destination, $siblingName, $isAfter, $alias) = $this->_scheduledStructure->getElementToMove($element);
        if (!$alias && false === $this->_structure->getChildId($destination, $this->getElementAlias($element))) {
            $alias = $this->getElementAlias($element);
        }
        $this->_structure->unsetChild($element, $alias)->setAsChild($element, $destination, $alias);
        $this->reorderChild($destination, $element, $siblingName, $isAfter);
        return $this;
    }

    /**
     * Traverse through all elements of specified XML-node and schedule structural elements of it
     *
     * @param \Magento\Framework\View\Layout\Element $parent
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _readStructure($parent)
    {
        foreach ($parent as $node) {
            /** @var $node \Magento\Framework\View\Layout\Element */
            switch ($node->getName()) {
                case Element::TYPE_CONTAINER:
                    $this->_scheduleStructure($node, $parent);
                    $this->_mergeContainerAttributes($node);
                    $this->_readStructure($node);
                    break;

                case Element::TYPE_BLOCK:
                    $this->_scheduleStructure($node, $parent);
                    $this->_readStructure($node);
                    break;

                case Element::TYPE_UI_COMPONENT:
                    $this->_scheduleStructure($node, $parent);
                    break;

                case Element::TYPE_REFERENCE_CONTAINER:
                    $this->_mergeContainerAttributes($node);
                    $this->_readStructure($node);
                    break;

                case Element::TYPE_REFERENCE_BLOCK:
                    $this->_readStructure($node);
                    break;

                case Element::TYPE_ACTION:
                    $referenceName = $parent->getAttribute('name');
                    $element = $this->_scheduledStructure->getStructureElement($referenceName, array());
                    $element['actions'][] = array($node, $parent);
                    $this->_scheduledStructure->setStructureElement($referenceName, $element);
                    break;

                case Element::TYPE_ARGUMENTS:
                    $referenceName = $parent->getAttribute('name');
                    $element = $this->_scheduledStructure->getStructureElement($referenceName, array());
                    $args = $this->_parseArguments($node);
                    $element['arguments'] = $this->_mergeArguments($element, $args);

                    $this->_scheduledStructure->setStructureElement($referenceName, $element);
                    break;

                case Element::TYPE_MOVE:
                    $this->_scheduleMove($node);
                    break;

                case Element::TYPE_REMOVE:
                    $this->_scheduledStructure->setElementToRemoveList((string)$node->getAttribute('name'));
                    break;

                case Page\Config::ELEMENT_TYPE_HTML:
                    $this->pageConfigReader->readHtml($node);
                    break;

                case Page\Config::ELEMENT_TYPE_HEAD:
                    $this->pageConfigReader->readHead($node);
                    break;

                case Page\Config::ELEMENT_TYPE_BODY:
                    $this->pageConfigReader->readBody($node);
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Merge Container attributes
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @return void
     */
    protected function _mergeContainerAttributes(\Magento\Framework\View\Layout\Element $node)
    {
        $containerName = $node->getAttribute('name');
        $element = $this->_scheduledStructure->getStructureElement($containerName, array());

        if (isset($element['attributes'])) {
            $keys = array_keys($element['attributes']);
            foreach ($keys as $key) {
                if (isset($node[$key])) {
                    $element['attributes'][$key] = (string)$node[$key];
                }
            }
        } else {
            $element['attributes'] = array(
                Element::CONTAINER_OPT_HTML_TAG => (string)$node[Element::CONTAINER_OPT_HTML_TAG],
                Element::CONTAINER_OPT_HTML_ID => (string)$node[Element::CONTAINER_OPT_HTML_ID],
                Element::CONTAINER_OPT_HTML_CLASS => (string)$node[Element::CONTAINER_OPT_HTML_CLASS],
                Element::CONTAINER_OPT_LABEL => (string)$node[Element::CONTAINER_OPT_LABEL]
            );
        }
        $this->_scheduledStructure->setStructureElement($containerName, $element);
    }

    /**
     * Merge element arguments
     *
     * @param array $element
     * @param array $arguments
     * @return array
     */
    protected function _mergeArguments(array $element, array $arguments)
    {
        $output = $arguments;
        if (isset($element['arguments'])) {
            $output = array_replace_recursive($element['arguments'], $arguments);
        }
        return $output;
    }

    /**
     * Parse argument nodes and return their array representation
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @return array
     */
    protected function _parseArguments(\Magento\Framework\View\Layout\Element $node)
    {
        $nodeDom = dom_import_simplexml($node);
        $result = array();
        foreach ($nodeDom->childNodes as $argumentNode) {
            if ($argumentNode instanceof \DOMElement && $argumentNode->nodeName == 'argument') {
                $argumentName = $argumentNode->getAttribute('name');
                $result[$argumentName] = $this->argumentParser->parse($argumentNode);
            }
        }
        return $result;
    }

    /**
     * Compute and return argument values
     *
     * @param array $arguments
     * @return array
     */
    protected function _evaluateArguments(array $arguments)
    {
        $result = array();
        foreach ($arguments as $argumentName => $argumentData) {
            $result[$argumentName] = $this->argumentInterpreter->evaluate($argumentData);
        }
        return $result;
    }

    /**
     * Schedule structural changes for move directive
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @throws \Magento\Framework\Exception
     * @return $this
     */
    protected function _scheduleMove($node)
    {
        $elementName = (string)$node->getAttribute('element');
        $destination = (string)$node->getAttribute('destination');
        $alias = (string)$node->getAttribute('as') ?: '';
        if ($elementName && $destination) {
            list($siblingName, $isAfter) = $this->_beforeAfterToSibling($node);
            $this->_scheduledStructure->setElementToMove(
                $elementName,
                array($destination, $siblingName, $isAfter, $alias)
            );
        } else {
            throw new \Magento\Framework\Exception('Element name and destination must be specified.');
        }
        return $this;
    }

    /**
     * Populate queue for generating structural elements
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @param \Magento\Framework\View\Layout\Element $parent
     * @return void
     * @see _scheduleElement() where the _scheduledStructure is used
     */
    protected function _scheduleStructure($node, $parent)
    {
        if ((string)$node->getAttribute('name')) {
            $name = (string)$node->getAttribute('name');
        } else {
            $name = $this->_generateAnonymousName($parent->getElementName() . '_schedule_block');
            $node->addAttribute('name', $name);
        }
        $path = $name;

        // type, alias, parentName, siblingName, isAfter, node
        $row = array(
            self::SCHEDULED_STRUCTURE_INDEX_NAME => $node->getName(),
            self::SCHEDULED_STRUCTURE_INDEX_ALIAS => '',
            self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME => '',
            self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME => null,
            self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER => true,
            self::SCHEDULED_STRUCTURE_INDEX_LAYOUT_ELEMENT => $node
        );

        $parentName = $parent->getElementName();
        if ($parentName) {
            $row[self::SCHEDULED_STRUCTURE_INDEX_ALIAS] = (string)$node->getAttribute('as');
            $row[self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME] = $parentName;

            list($row[self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME],
                $row[self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER]) = $this->_beforeAfterToSibling(
                    $node
                );

            // materialized path for referencing nodes in the plain array of _scheduledStructure
            if ($this->_scheduledStructure->hasPath($parentName)) {
                $path = $this->_scheduledStructure->getPath($parentName) . '/' . $path;
            }
        }

        $this->_overrideElementWorkaround($name, $path);
        $this->_scheduledStructure->setPathElement($name, $path);
        if ($this->_scheduledStructure->hasStructureElement($name)) {
            // union of arrays
            $this->_scheduledStructure->setStructureElement(
                $name,
                $row + $this->_scheduledStructure->getStructureElement($name)
            );
        } else {
            $this->_scheduledStructure->setStructureElement($name, $row);
        }
    }

    /**
     * Analyze "before" and "after" information in the node and return sibling name and whether "after" or "before"
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @return array
     */
    protected function _beforeAfterToSibling($node)
    {
        $result = array(null, true);
        if (isset($node['after'])) {
            $result[0] = (string)$node['after'];
        } elseif (isset($node['before'])) {
            $result[0] = (string)$node['before'];
            $result[1] = false;
        }
        return $result;
    }

    /**
     * Destroy previous element with same name and all its children, if new element overrides it
     *
     * This is a workaround to handle situation, when an element emerges with name of element that already exists.
     * In this case we destroy entire structure of the former element and replace with the new one.
     *
     * @param string $name
     * @param string $path
     * @return void
     */
    protected function _overrideElementWorkaround($name, $path)
    {
        if ($this->_scheduledStructure->hasStructureElement($name)) {
            foreach ($this->_scheduledStructure->getPaths() as $potentialChild => $childPath) {
                if (0 === strpos($childPath, "{$path}/")) {
                    $this->_scheduledStructure->unsetPathElement($potentialChild);
                    $this->_scheduledStructure->unsetStructureElement($potentialChild);
                }
            }
        }
    }

    /**
     * Process queue of structural elements and actually add them to structure, and schedule elements for generation
     *
     * The catch is to populate parents first, if they are not in the structure yet.
     * Since layout updates could come in arbitrary order, a case is possible where an element is declared in reference,
     * while referenced element itself is not declared yet.
     *
     * @param string $key in _scheduledStructure represent element name
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _scheduleElement($key)
    {
        $row = $this->_scheduledStructure->getStructureElement($key);

        if (!isset($row[self::SCHEDULED_STRUCTURE_INDEX_LAYOUT_ELEMENT])) {
            $this->_logger->log("Broken reference: missing declaration of the element '{$key}'.", \Zend_Log::CRIT);
            $this->_scheduledStructure->unsetPathElement($key);
            $this->_scheduledStructure->unsetStructureElement($key);
            return;
        }
        list($type, $alias, $parentName, $siblingName, $isAfter, $node) = $row;
        $name = $this->_createStructuralElement($key, $type, $parentName . $alias);
        if ($parentName) {
            // recursively populate parent first
            if ($this->_scheduledStructure->hasStructureElement($parentName)) {
                $this->_scheduleElement($parentName, $this->_scheduledStructure->getStructureElement($parentName));
            }
            if ($this->_structure->hasElement($parentName)) {
                try {
                    $this->_structure->setAsChild($name, $parentName, $alias);
                } catch (\Exception $e) {
                    $this->_logger->log($e->getMessage());
                }
            } else {
                $this->_logger->log(
                    "Broken reference: the '{$name}' element cannot be added as child to '{$parentName}', " .
                    'because the latter doesn\'t exist',
                    \Zend_Log::CRIT
                );
            }
        }
        $this->_scheduledStructure->unsetStructureElement($key);
        $data = array(
            $type,
            $node,
            isset($row['actions']) ? $row['actions'] : array(),
            isset($row['arguments']) ? $row['arguments'] : array(),
            isset($row['attributes']) ? $row['attributes'] : array()
        );
        $this->_scheduledStructure->setElement($name, $data);

        /**
         * Some elements provide info "after" or "before" which sibling they are supposed to go
         * Make sure to populate these siblings as well and order them correctly
         */
        if ($siblingName) {
            if ($this->_scheduledStructure->hasStructureElement($siblingName)) {
                $this->_scheduleElement($siblingName);
            }
            $this->reorderChild($parentName, $name, $siblingName, $isAfter);
        }
    }

    /**
     * Register an element in structure
     *
     * Will assign an "anonymous" name to the element, if provided with an empty name
     *
     * @param string $name
     * @param string $type
     * @param string $class
     * @return string
     */
    protected function _createStructuralElement($name, $type, $class)
    {
        if (empty($name)) {
            $name = $this->_generateAnonymousName($class);
        }
        $this->_structure->createElement($name, array('type' => $type));
        return $name;
    }

    /**
     * Generate anonymous element name for structure
     *
     * @param string $class
     * @return string
     */
    protected function _generateAnonymousName($class)
    {
        $position = strpos($class, '\\Block\\');
        $key = $position !== false ? substr($class, $position + 7) : $class;
        $key = strtolower(trim($key, '_'));

        if (!isset($this->_nameIncrement[$key])) {
            $this->_nameIncrement[$key] = 0;
        }

        if ($this->_nameIncrement[$key] == 0 && !$this->_structure->hasElement($key)) {
            $this->_nameIncrement[$key]++;
            return $key;
        }

        do {
            $name = $key . '_' . $this->_nameIncrement[$key]++;
        } while ($this->_structure->hasElement($name));

        return $name;
    }

    /**
     * Creates block object based on xml node data and add it to the layout
     *
     * @param string $elementName
     * @return \Magento\Framework\View\Element\AbstractBlock|void
     * @throws \Magento\Framework\Exception
     */
    protected function _generateBlock($elementName)
    {
        list($type, $node, $actions, $args) = $this->_scheduledStructure->getElement($elementName);
        if ($type !== Element::TYPE_BLOCK) {
            throw new \Magento\Framework\Exception("Unexpected element type specified for generating block: {$type}.");
        }


        $configPath = (string)$node->getAttribute('ifconfig');
        if (!empty($configPath)
            && !$this->_scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
        ) {
            $this->_scheduledStructure->unsetElement($elementName);
            return;
        }

        $group = (string)$node->getAttribute('group');
        if (!empty($group)) {
            $this->_structure->addToParentGroup($elementName, $group);
        }

        // create block
        $className = (string)$node['class'];

        $arguments = $this->_evaluateArguments($args);

        $block = $this->_createBlock($className, $elementName, array('data' => $arguments));

        if (!empty($node['template'])) {
            $templateFileName = (string)$node['template'];
            $block->setTemplate($templateFileName);
        }

        if (!empty($node['ttl'])) {
            $ttl = (int)$node['ttl'];
            $block->setTtl($ttl);
        }

        $this->_scheduledStructure->unsetElement($elementName);

        // execute block methods
        foreach ($actions as $action) {
            list($actionNode, $parent) = $action;
            $this->_generateAction($actionNode, $parent);
        }

        return $block;
    }

    /**
     * Creates UI Component object based on xml node data and add it to the layout
     *
     * @param string $elementName
     * @return \Magento\Framework\View\Element\AbstractBlock|void
     * @throws \Magento\Framework\Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _generateUiComponent($elementName)
    {
        list($type, $node, $actions, $args) = $this->_scheduledStructure->getElement($elementName);
        if ($type !== Element::TYPE_UI_COMPONENT) {
            throw new \Magento\Framework\Exception(
                "Unexpected element type specified for generating UI Component: {$type}."
            );
        }

        $configPath = (string)$node->getAttribute('ifconfig');
        if (!empty($configPath)
            && !$this->_scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
        ) {
            $this->_scheduledStructure->unsetElement($elementName);
            return;
        }

        $group = (string)$node->getAttribute('group');
        if (!empty($group)) {
            $this->_structure->addToParentGroup($elementName, $group);
        }

        $arguments = $this->_evaluateArguments($args);

        // create Ui Component Object
        $componentName = (string)$node['component'];

        $uiComponent = $this->_uiComponentFactory->createUiComponent($componentName, $elementName, $arguments);

        $this->_blocks[$elementName] = $uiComponent;

        $this->_scheduledStructure->unsetElement($elementName);

        return $uiComponent;
    }

    /**
     * Set container-specific data to structure element
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @return void
     * @throws \Magento\Framework\Exception If any of arguments are invalid
     */
    protected function _generateContainer($name, $label, array $options)
    {
        $this->_structure->setAttribute($name, Element::CONTAINER_OPT_LABEL, $label);
        unset($options[Element::CONTAINER_OPT_LABEL]);
        unset($options['type']);
        $allowedTags = array(
            'dd',
            'div',
            'dl',
            'fieldset',
            'header',
            'footer',
            'hgroup',
            'ol',
            'p',
            'section',
            'table',
            'tfoot',
            'ul'
        );
        if (!empty($options[Element::CONTAINER_OPT_HTML_TAG]) && !in_array(
            $options[Element::CONTAINER_OPT_HTML_TAG],
            $allowedTags
        )
        ) {
            throw new \Magento\Framework\Exception(
                __(
                    'Html tag "%1" is forbidden for usage in containers. Consider to use one of the allowed: %2.',
                    $options[Element::CONTAINER_OPT_HTML_TAG],
                    implode(', ', $allowedTags)
                )
            );
        }
        if (empty($options[Element::CONTAINER_OPT_HTML_TAG]) && (!empty($options[Element::CONTAINER_OPT_HTML_ID]) ||
            !empty($options[Element::CONTAINER_OPT_HTML_CLASS]))
        ) {
            throw new \Magento\Framework\Exception(
                'HTML ID or class will not have effect, if HTML tag is not specified.'
            );
        }
        foreach ($options as $key => $value) {
            $this->_structure->setAttribute($name, $key, $value);
        }
    }

    /**
     * Run action defined in layout update
     *
     * @param \Magento\Framework\View\Layout\Element $node
     * @param \Magento\Framework\View\Layout\Element $parent
     * @return void
     */
    protected function _generateAction($node, $parent)
    {
        $configPath = $node->getAttribute('ifconfig');
        if ($configPath
            && !$this->_scopeConfig->isSetFlag($configPath, $this->scopeType, $this->scopeResolver->getScope())
        ) {
            return;
        }

        $method = $node->getAttribute('method');
        $parentName = $node->getAttribute('block');
        if (empty($parentName)) {
            $parentName = $parent->getElementName();
        }

        $profilerKey = 'BLOCK_ACTION:' . $parentName . '>' . $method;
        \Magento\Framework\Profiler::start($profilerKey);

        $block = $this->getBlock($parentName);
        if (!empty($block)) {
            $args = $this->_parseArguments($node);
            $args = $this->_evaluateArguments($args);
            call_user_func_array(array($block, $method), $args);
        }

        \Magento\Framework\Profiler::stop($profilerKey);
    }

    /**
     * Get child block if exists
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function getChildBlock($parentName, $alias)
    {
        $name = $this->_structure->getChildId($parentName, $alias);
        if ($this->isBlock($name)) {
            return $this->getBlock($name);
        }
        return false;
    }

    /**
     * Set child element into layout structure
     *
     * @param string $parentName
     * @param string $elementName
     * @param string $alias
     * @return $this
     */
    public function setChild($parentName, $elementName, $alias)
    {
        $this->_structure->setAsChild($elementName, $parentName, $alias);
        return $this;
    }

    /**
     * Reorder a child of a specified element
     *
     * If $offsetOrSibling is null, it will put the element to the end
     * If $offsetOrSibling is numeric (integer) value, it will put the element after/before specified position
     * Otherwise -- after/before specified sibling
     *
     * @param string $parentName
     * @param string $childName
     * @param string|int|null $offsetOrSibling
     * @param bool $after
     * @return void
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true)
    {
        if (is_numeric($offsetOrSibling)) {
            $offset = (int)abs($offsetOrSibling) * ($after ? 1 : -1);
            $this->_structure->reorderChild($parentName, $childName, $offset);
        } elseif (null === $offsetOrSibling) {
            $this->_structure->reorderChild($parentName, $childName, null);
        } else {
            $children = $this->getChildNames($parentName);
            if ($this->_structure->getChildId($parentName, $offsetOrSibling) !== false) {
                $offsetOrSibling = $this->_structure->getChildId($parentName, $offsetOrSibling);
            }
            $sibling = $this->_filterSearchMinus($offsetOrSibling, $children, $after);
            if ($childName !== $sibling) {
                $siblingParentName = $this->_structure->getParentId($sibling);
                if ($parentName !== $siblingParentName) {
                    $this->_logger->log(
                        "Broken reference: the '{$childName}' tries to reorder itself towards '{$sibling}', but " .
                        "their parents are different: '{$parentName}' and '{$siblingParentName}' respectively.",
                        \Zend_Log::CRIT
                    );
                    return;
                }
                $this->_structure->reorderToSibling($parentName, $childName, $sibling, $after ? 1 : -1);
            }
        }
    }

    /**
     * Search for an array element using needle, but needle may be '-', which means "first" or "last" element
     *
     * Returns first or last element in the haystack, or the $needle argument
     *
     * @param string $needle
     * @param array $haystack
     * @param bool $isLast
     * @return string
     */
    protected function _filterSearchMinus($needle, array $haystack, $isLast)
    {
        if ('-' === $needle) {
            if ($isLast) {
                return array_pop($haystack);
            }
            return array_shift($haystack);
        }
        return $needle;
    }

    /**
     * Remove child element from parent
     *
     * @param string $parentName
     * @param string $alias
     * @return $this
     */
    public function unsetChild($parentName, $alias)
    {
        $this->_structure->unsetChild($parentName, $alias);
        return $this;
    }

    /**
     * Get list of child names
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName)
    {
        return array_keys($this->_structure->getChildren($parentName));
    }

    /**
     * Get list of child blocks
     *
     * Returns associative array of <alias> => <block instance>
     *
     * @param string $parentName
     * @return array
     */
    public function getChildBlocks($parentName)
    {
        $blocks = array();
        foreach ($this->_structure->getChildren($parentName) as $childName => $alias) {
            $block = $this->getBlock($childName);
            if ($block) {
                $blocks[$alias] = $block;
            }
        }
        return $blocks;
    }

    /**
     * Get child name by alias
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|string
     */
    public function getChildName($parentName, $alias)
    {
        return $this->_structure->getChildId($parentName, $alias);
    }

    /**
     * Find an element in layout, render it and return string with its output
     *
     * @param string $name
     * @param bool $useCache
     * @return string
     */
    public function renderElement($name, $useCache = true)
    {
        if (!isset($this->_renderElementCache[$name]) || !$useCache) {
            if ($this->isUiComponent($name)) {
                $result = $this->_renderUiComponent($name);
            } else if ($this->isBlock($name)) {
                $result = $this->_renderBlock($name);
            } else {
                $result = $this->_renderContainer($name);
            }
            $this->_renderElementCache[$name] = $result;
        }
        $this->_renderingOutput->setData('output', $this->_renderElementCache[$name]);
        $this->_eventManager->dispatch(
            'core_layout_render_element',
            array('element_name' => $name, 'layout' => $this, 'transport' => $this->_renderingOutput)
        );
        return $this->_renderingOutput->getData('output');
    }

    /**
     * Gets HTML of block element
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception
     */
    protected function _renderBlock($name)
    {
        $block = $this->getBlock($name);
        return $block ? $block->toHtml() : '';
    }

    /**
     * Gets HTML of Ui Component
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception
     */
    protected function _renderUiComponent($name)
    {
        $uiComponent = $this->getUiComponent($name);
        return $uiComponent ? $uiComponent->toHtml() : '';
    }

    /**
     * Gets HTML of container element
     *
     * @param string $name
     * @return string
     */
    protected function _renderContainer($name)
    {
        $html = '';
        $children = $this->getChildNames($name);
        foreach ($children as $child) {
            $html .= $this->renderElement($child);
        }
        if ($html == '' || !$this->_structure->getAttribute($name, Element::CONTAINER_OPT_HTML_TAG)) {
            return $html;
        }

        $htmlId = $this->_structure->getAttribute($name, Element::CONTAINER_OPT_HTML_ID);
        if ($htmlId) {
            $htmlId = ' id="' . $htmlId . '"';
        }

        $htmlClass = $this->_structure->getAttribute($name, Element::CONTAINER_OPT_HTML_CLASS);
        if ($htmlClass) {
            $htmlClass = ' class="' . $htmlClass . '"';
        }

        $htmlTag = $this->_structure->getAttribute($name, Element::CONTAINER_OPT_HTML_TAG);

        $html = sprintf('<%1$s%2$s%3$s>%4$s</%1$s>', $htmlTag, $htmlId, $htmlClass, $html);

        return $html;
    }

    /**
     * Add element to parent group
     *
     * @param string $blockName
     * @param string $parentGroupName
     * @return bool
     */
    public function addToParentGroup($blockName, $parentGroupName)
    {
        return $this->_structure->addToParentGroup($blockName, $parentGroupName);
    }

    /**
     * Get element names for specified group
     *
     * @param string $blockName
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($blockName, $groupName)
    {
        return $this->_structure->getGroupChildNames($blockName, $groupName);
    }

    /**
     * Check if element exists in layout structure
     *
     * @param string $name
     * @return bool
     */
    public function hasElement($name)
    {
        return $this->_structure->hasElement($name);
    }

    /**
     * Get property value of an element
     *
     * @param string $name
     * @param string $attribute
     * @return mixed
     */
    public function getElementProperty($name, $attribute)
    {
        return $this->_structure->getAttribute($name, $attribute);
    }

    /**
     * Whether specified element is a block
     *
     * @param string $name
     * @return bool
     */
    public function isBlock($name)
    {
        if ($this->_structure->hasElement($name)) {
            return Element::TYPE_BLOCK === $this->_structure->getAttribute($name, 'type');
        }
        return false;
    }

    /**
     * Whether specified element is a UI Component
     *
     * @param string $name
     * @return bool
     */
    public function isUiComponent($name)
    {
        if ($this->_structure->hasElement($name)) {
            return Element::TYPE_UI_COMPONENT === $this->_structure->getAttribute($name, 'type');
        }
        return false;
    }

    /**
     * Checks if element with specified name is container
     *
     * @param string $name
     * @return bool
     */
    public function isContainer($name)
    {
        if ($this->_structure->hasElement($name)) {
            return Element::TYPE_CONTAINER === $this->_structure->getAttribute($name, 'type');
        }
        return false;
    }

    /**
     * Whether the specified element may be manipulated externally
     *
     * @param string $name
     * @return bool
     */
    public function isManipulationAllowed($name)
    {
        $parentName = $this->_structure->getParentId($name);
        return $parentName && $this->isContainer($parentName);
    }

    /**
     * Save block in blocks registry
     *
     * @param string $name
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function setBlock($name, $block)
    {
        $this->_blocks[$name] = $block;
        return $this;
    }

    /**
     * Remove block from registry
     *
     * @param string $name
     * @return $this
     */
    public function unsetElement($name)
    {
        if (isset($this->_blocks[$name])) {
            $this->_blocks[$name] = null;
            unset($this->_blocks[$name]);
        }
        $this->_structure->unsetElement($name);

        return $this;
    }

    /**
     * Block Factory
     *
     * @param  string $type
     * @param  string $name
     * @param  array $attributes
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function createBlock($type, $name = '', array $attributes = array())
    {
        $name = $this->_createStructuralElement($name, Element::TYPE_BLOCK, $type);
        $block = $this->_createBlock($type, $name, $attributes);
        return $block;
    }

    /**
     * Create block and add to layout
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param string $name
     * @param array $attributes
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _createBlock($block, $name, array $attributes = array())
    {
        $block = $this->_getBlockInstance($block, $attributes);

        $block->setType(get_class($block));
        $block->setNameInLayout($name);
        $block->addData(isset($attributes['data']) ? $attributes['data'] : array());
        $block->setLayout($this);

        $this->_blocks[$name] = $block;
        $this->_eventManager->dispatch('core_layout_block_create_after', array('block' => $block));
        return $this->_blocks[$name];
    }

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param string $name
     * @param string $parent
     * @param string $alias
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '')
    {
        if (empty($name) && $block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $name = $block->getNameInLayout();
        }
        $name = $this->_createStructuralElement(
            $name,
            Element::TYPE_BLOCK,
            $name ?: (is_object($block) ? get_class($block) : $block)
        );
        if ($parent) {
            $this->_structure->setAsChild($name, $parent, $alias);
        }
        return $this->_createBlock($block, $name);
    }

    /**
     * Insert container into layout structure
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @param string $parent
     * @param string $alias
     * @return void
     */
    public function addContainer($name, $label, array $options = array(), $parent = '', $alias = '')
    {
        $name = $this->_createStructuralElement($name, Element::TYPE_CONTAINER, $alias);
        $this->_generateContainer($name, $label, $options);
        if ($parent) {
            $this->_structure->setAsChild($name, $parent, $alias);
        }
    }

    /**
     * Rename element in layout and layout structure
     *
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function renameElement($oldName, $newName)
    {
        if (isset($this->_blocks[$oldName])) {
            $block = $this->_blocks[$oldName];
            $this->_blocks[$oldName] = null;
            unset($this->_blocks[$oldName]);
            $this->_blocks[$newName] = $block;
        }
        $this->_structure->renameElement($oldName, $newName);

        return $this;
    }

    /**
     * Create block object instance based on block type
     *
     * @param string|\Magento\Framework\View\Element\AbstractBlock $block
     * @param array $attributes
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _getBlockInstance($block, array $attributes = array())
    {
        if ($block && is_string($block)) {
            try {
                $block = $this->_blockFactory->createBlock($block, $attributes);
            } catch (\ReflectionException $e) {
                $this->_logger->log($e->getMessage());
            }
        }
        if (!$block instanceof \Magento\Framework\View\Element\AbstractBlock) {
            throw new \Magento\Framework\Model\Exception(__('Invalid block type: %1', $block));
        }
        return $block;
    }

    /**
     * Retrieve all blocks from registry as array
     *
     * @return array
     */
    public function getAllBlocks()
    {
        return $this->_blocks;
    }

    /**
     * Get block object by name
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getBlock($name)
    {
        if ($this->_scheduledStructure->hasElement($name)) {
            $this->_generateBlock($name);
        }
        if (isset($this->_blocks[$name])) {
            return $this->_blocks[$name];
        } else {
            return false;
        }
    }

    /**
     * Get Ui Component object by name
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getUiComponent($name)
    {
        if ($this->_scheduledStructure->hasElement($name)) {
            $this->_generateUiComponent($name);
        }
        if (isset($this->_blocks[$name])) {
            return $this->_blocks[$name];
        } else {
            return false;
        }
    }

    /**
     * Gets parent name of an element with specified name
     *
     * @param string $childName
     * @return bool|string
     */
    public function getParentName($childName)
    {
        return $this->_structure->getParentId($childName);
    }

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return bool|string
     */
    public function getElementAlias($name)
    {
        return $this->_structure->getChildAlias($this->_structure->getParentId($name), $name);
    }

    /**
     * Add an element to output
     *
     * @param string $name
     * @return $this
     */
    public function addOutputElement($name)
    {
        $this->_output[$name] = $name;
        return $this;
    }

    /**
     * Remove an element from output
     *
     * @param string $name
     * @return $this
     */
    public function removeOutputElement($name)
    {
        if (isset($this->_output[$name])) {
            unset($this->_output[$name]);
        }
        return $this;
    }

    /**
     * Get all blocks marked for output
     *
     * @return string
     */
    public function getOutput()
    {
        $out = '';
        foreach ($this->_output as $name) {
            $out .= $this->renderElement($name);
        }

        return $out;
    }

    /**
     * Retrieve messages block
     *
     * @return \Magento\Framework\View\Element\Messages
     */
    public function getMessagesBlock()
    {
        $block = $this->getBlock('messages');
        if ($block) {
            return $block;
        }
        return $this->createBlock('Magento\Framework\View\Element\Messages', 'messages');
    }

    /**
     * Get block singleton
     *
     * @param string $type
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \Magento\Framework\Model\Exception
     */
    public function getBlockSingleton($type)
    {
        if (!isset($this->_helpers[$type])) {
            if (!$type) {
                throw new \Magento\Framework\Model\Exception('Invalid block type');
            }

            $helper = $this->_blockFactory->createBlock($type);
            if ($helper) {
                if ($helper instanceof \Magento\Framework\View\Element\AbstractBlock) {
                    $helper->setLayout($this);
                }
                $this->_helpers[$type] = $helper;
            }
        }
        return $this->_helpers[$type];
    }

    /**
     * Retrieve block factory
     *
     * @return \Magento\Framework\View\Element\BlockFactory
     */
    public function getBlockFactory()
    {
        return $this->_blockFactory;
    }

    /**
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @param string $type
     * @param string $template
     * @param array $data
     * @return $this
     */
    public function addAdjustableRenderer($namespace, $staticType, $dynamicType, $type, $template, $data = array())
    {
        $this->_renderers[$namespace][$staticType][$dynamicType] = array(
            'type' => $type,
            'template' => $template,
            'data' => $data
        );
        return $this;
    }

    /**
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @return array|null
     */
    public function getRendererOptions($namespace, $staticType, $dynamicType)
    {
        if (!isset($this->_renderers[$namespace])) {
            return null;
        }
        if (!isset($this->_renderers[$namespace][$staticType])) {
            return null;
        }
        if (!isset($this->_renderers[$namespace][$staticType][$dynamicType])) {
            return null;
        }
        return $this->_renderers[$namespace][$staticType][$dynamicType];
    }

    /**
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @param array $data
     * @return void
     */
    public function executeRenderer($namespace, $staticType, $dynamicType, $data = array())
    {
        if ($options = $this->getRendererOptions($namespace, $staticType, $dynamicType)) {
            $dictionary = array();
            /** @var $block \Magento\Framework\View\Element\Template */
            $block = $this->createBlock($options['type'], '')
                ->setData($data)
                ->assign($dictionary)
                ->setTemplate($options['template'])
                ->assign($data);

            echo $this->_renderBlock($block->getNameInLayout());
        }
    }

    /**
     * Init messages by message storage(s), loading and adding messages to layout messages block
     *
     * @param string|array $messageGroups
     * @return void
     * @throws \UnexpectedValueException
     */
    public function initMessages($messageGroups = array())
    {
        foreach ($this->_prepareMessageGroup($messageGroups) as $group) {
            $block = $this->getMessagesBlock();
            $block->addMessages($this->messageManager->getMessages(true, $group));
            $block->addStorageType($group);
        }
    }

    /**
     * Validate message groups
     *
     * @param array $messageGroups
     * @return array
     */
    protected function _prepareMessageGroup($messageGroups)
    {
        if (!is_array($messageGroups)) {
            $messageGroups = array($messageGroups);
        } elseif (empty($messageGroups)) {
            $messageGroups[] = $this->messageManager->getDefaultGroup();
        }
        return $messageGroups;
    }

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isCacheable()
    {
        $cacheableXml = !(bool)count($this->_xml->xpath('//' . Element::TYPE_BLOCK . '[@cacheable="false"]'));
        return $this->cacheable && $cacheableXml;
    }

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * Mark layout as private
     *
     * @param bool $isPrivate
     * @return Layout
     */
    public function setIsPrivate($isPrivate = true)
    {
        $this->isPrivate = (bool)$isPrivate;
        return $this;
    }
}
