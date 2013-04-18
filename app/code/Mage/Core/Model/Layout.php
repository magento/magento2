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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Layout model
 *
 * @category    Mage
 * @package     Mage_Core
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Mage_Core_Model_Layout extends Varien_Simplexml_Config
{
    /**#@+
     * Supported layout directives
     */
    const TYPE_BLOCK        = 'block';
    const TYPE_CONTAINER    = 'container';
    const TYPE_ACTION       = 'action';
    const TYPE_ARGUMENTS    = 'arguments';
    const TYPE_REFERENCE    = 'reference';
    const TYPE_REMOVE       = 'remove';
    const TYPE_MOVE         = 'move';
    /**#@-*/

    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG   = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID    = 'htmlId';
    const CONTAINER_OPT_LABEL      = 'label';
    /**#@-*/

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
     * Layout Update module
     *
     * @var Mage_Core_Model_Layout_Merge
     */
    protected $_update;

    /**
     * @var Mage_Core_Model_BlockFactory
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
     * Layout area (f.e. admin, frontend)
     *
     * @var string
     */
    protected $_area;

    /**
     * Helper blocks cache for this layout
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Flag to have blocks' output go directly to browser as oppose to return result
     *
     * @var boolean
     */
    protected $_directOutput = false;

    /**
     * A variable for transporting output into observer during rendering
     *
     * @var Varien_Object
     */
    protected $_renderingOutput = null;

    /**
     * Cache of generated elements' HTML
     *
     * @var array
     */
    protected $_renderElementCache = array();

    /**
     * Layout structure model
     *
     * @var Magento_Data_Structure
     */
    protected $_structure;

    /**
     * An increment to generate names
     *
     * @var int
     */
    protected $_nameIncrement = array();

    /**
     * @var Mage_Core_Model_Layout_Argument_Processor
     */
    protected $_argumentProcessor;

    /**
     * @var Mage_Core_Model_Layout_ScheduledStructure
     */
    protected $_scheduledStructure;

    /**
     * @var Mage_Core_Model_Layout_Translator
     */
    protected $_translator;

    protected $_datasources = array();

    protected $_dataSourceFactory;

    /**
     * @param Mage_Core_Model_BlockFactory $blockFactory
     * @param Magento_Data_Structure $structure
     * @param Mage_Core_Model_Layout_Argument_Processor $argumentProcessor
     * @param Mage_Core_Model_Layout_Translator $translator
     * @param Mage_Core_Model_Layout_ScheduledStructure $scheduledStructure
     * @param string $area
     */
    public function __construct(
        Mage_Core_Model_BlockFactory $blockFactory,
        Magento_Data_Structure $structure,
        Mage_Core_Model_Layout_Argument_Processor $argumentProcessor,
        Mage_Core_Model_Layout_Translator $translator,
        Mage_Core_Model_Layout_ScheduledStructure $scheduledStructure,
        Magento_Datasource_Factory $dataSourceFactory,
        $area = Mage_Core_Model_Design_Package::DEFAULT_AREA
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_area = $area;
        $this->_structure = $structure;
        $this->_argumentProcessor = $argumentProcessor;
        $this->_translator = $translator;
        $this->_elementClass = 'Mage_Core_Model_Layout_Element';
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        $this->_renderingOutput = new Varien_Object;
        $this->_scheduledStructure = $scheduledStructure;
        $this->_dataSourceFactory = $dataSourceFactory;
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
     * @return Mage_Core_Model_Layout_Merge
     */
    public function getUpdate()
    {
        if (!$this->_update) {
            $arguments = array('area' => $this->getArea());
            $this->_update = Mage::getModel('Mage_Core_Model_Layout_Merge', array('arguments' => $arguments));
        }
        return $this->_update;
    }

    /**
     * Retrieve layout area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_area;
    }

    /**
     * Declaring layout direct output flag
     *
     * @param   bool $flag
     * @return  Mage_Core_Model_Layout
     */
    public function setDirectOutput($flag)
    {
        $this->_directOutput = $flag;
        return $this;
    }

    /**
     * Retrieve direct output flag
     *
     * @return bool
     */
    public function isDirectOutput()
    {
        return $this->_directOutput;
    }

    /**
     * Layout xml generation
     *
     * @return Mage_Core_Model_Layout
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
     */
    public function generateElements()
    {
        Magento_Profiler::start(__CLASS__ . '::' . __METHOD__);
        Magento_Profiler::start('build_structure');

        $this->_scheduledStructure->flushScheduledStructure();

        $this->_readStructure($this->getNode());

        $this->_dataSourceFactory
            ->init($this->getDatasources());

        while (false === $this->_scheduledStructure->isStructureEmpty()) {
            $this->_scheduleElement(key($this->_scheduledStructure->getStructure()));
        };
        $this->_scheduledStructure->flushPaths();

        foreach ($this->_scheduledStructure->getListToMove() as $elementToMove) {
            $this->_moveElementInStructure($elementToMove);
        }

        foreach ($this->_scheduledStructure->getListToRemove() as $elementToRemove) {
            $this->_removeElement($elementToRemove);
        }

        Magento_Profiler::stop('build_structure');

        Magento_Profiler::start('generate_elements');

        while (false === $this->_scheduledStructure->isElementsEmpty()) {
            list($type, $node) = current($this->_scheduledStructure->getElements());
            $elementName = key($this->_scheduledStructure->getElements());

            if (isset($node['output'])) {
                $this->addOutputElement($elementName);
            }
            if ($type == self::TYPE_BLOCK) {
                $this->_generateBlock($elementName);
            } else {
                $this->_generateContainer($elementName, (string)$node[self::CONTAINER_OPT_LABEL], array(
                    self::CONTAINER_OPT_HTML_TAG => (string)$node[self::CONTAINER_OPT_HTML_TAG],
                    self::CONTAINER_OPT_HTML_ID => (string)$node[self::CONTAINER_OPT_HTML_ID],
                    self::CONTAINER_OPT_HTML_CLASS => (string)$node[self::CONTAINER_OPT_HTML_CLASS]
                ));
                $this->_scheduledStructure->unsetElement($elementName);
            }
        }
        Magento_Profiler::stop('generate_elements');
        Magento_Profiler::stop(__CLASS__ . '::' . __METHOD__);
    }

    /**
     * Remove scheduled element
     *
     * @param string $elementName
     * @param bool $isChild
     * @return Mage_Core_Model_Layout
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
     * @return Mage_Core_Model_Layout
     */
    protected function _moveElementInStructure($element)
    {
        list ($destination, $siblingName, $isAfter, $alias) = $this->_scheduledStructure->getElementToMove($element);
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
     * @param Mage_Core_Model_Layout_Element $parent
     */
    protected function _readStructure($parent)
    {
        foreach ($parent as $node) {
            /** @var $node Mage_Core_Model_Layout_Element */
            switch ($node->getName()) {
                case self::TYPE_CONTAINER:
                case self::TYPE_BLOCK:
                    $this->_initDataSources($node);
                    $this->_scheduleStructure($node, $parent);
                    $this->_readStructure($node);
                    break;

                case self::TYPE_REFERENCE:
                    $this->_readStructure($node);
                    break;

                case self::TYPE_ACTION:
                    $referenceName = $parent->getAttribute('name');
                    $element = $this->_scheduledStructure->getStructureElement($referenceName, array());
                    $element['actions'][] = array($node, $parent);
                    $this->_scheduledStructure->setStructureElement($referenceName, $element);
                    break;

                case self::TYPE_ARGUMENTS:
                    $referenceName = $parent->getAttribute('name');
                    $element = $this->_scheduledStructure->getStructureElement($referenceName, array());
                    $args = $this->_readArguments($node);
                    $element['arguments'] = $this->_mergeArguments($element, $args);

                    $this->_scheduledStructure->setStructureElement($referenceName, $element);
                    break;

                case self::TYPE_MOVE:
                    $this->_scheduleMove($node);
                    break;

                case self::TYPE_REMOVE:
                    $this->_scheduledStructure->setElementToRemoveList((string)$node->getAttribute('name'));
                    break;
            }
        }
    }

    /**
     * Grab information about data source from the node
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return Mage_Core_Model_Layout
     */
    protected function _initDataSources($node)
    {
        if (!$dataSources = $node->xpath('data')) {
            return $this;
        }
        $nodeName = $node->getAttribute('name');
        foreach ($dataSources as $dataSourceNode) {
            $dataSourceName = $dataSourceNode->getAttribute('service-call');
            if (isset($this->_datasources[$dataSourceName])) {
                $this->_datasources[$dataSourceName]['namespaces'][$nodeName] =
                    $dataSourceNode->getAttribute('alias');
            } else {
                $this->_datasources[$dataSourceName] = array(
                    'namespaces' => array($nodeName => $dataSourceNode->getAttribute('alias'))
                );
            }
        }
        return $this;
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
     * Read arguments node and create prepared array of them
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return array
     */
    protected function _readArguments(Mage_Core_Model_Layout_Element $node)
    {
        $arguments = array();
        $moduleName = isset($node['module']) ? (string)$node['module'] : null;

        foreach ($node->children() as $argument) {
            /** @var $argument Mage_Core_Model_Layout_Element */

            $type = $argument->getAttribute('type');
            if (null !== $type) {
                $arguments[$argument->getName()]['type'] = $type;
            }

            if ($argument->hasChildren()) {
                $value = array();
                $this->_fillArgumentsArray($argument, $value, $moduleName);
                unset($value['updater']);
                unset($value['@']);

                $updaters = $this->_getArgumentUpdaters($argument);

                if (false === empty($updaters)) {
                    $arguments[$argument->getName()]['updater'] = $updaters;
                }

                if (is_array($value) && !empty($value)) {
                    $arguments[$argument->getName()]['value'] = $value;
                }
            } else {
                $value = $this->_translator->translateArgument($argument, $moduleName);
                if ('' !== $value) {
                    $arguments[$argument->getName()]['value'] = $value;
                }
            }
        }
        return $arguments;
    }

    protected function _fillArgumentsArray(Mage_Core_Model_Layout_Element $node, &$argumentsArray, $moduleName)
    {
        $moduleName = isset($node['module']) ? (string)$node['module'] : $moduleName;

        /** @var $childNode Mage_Core_Model_Layout_Element */
        foreach ($node->children() as $childNode) {
            $nodeName = $childNode->getName();
            if ($childNode->hasChildren()) {
                $this->_fillArgumentsArray($childNode, $argumentsArray[$nodeName], $moduleName);
            } else {
                $argumentsArray[$nodeName] = $this->_translator->translateArgument($childNode, $moduleName);
            }
        }
    }

    /**
     * Get argument updaters list
     *
     * @param Mage_Core_Model_Layout_Element $argument
     * @return array
     */
    protected function _getArgumentUpdaters(Mage_Core_Model_Layout_Element $argument)
    {
        $updaters = array();
        foreach ($argument->children() as $argumentChild) {
            /** @var $argumentChild Mage_Core_Model_Layout_Element */
            if ('updater' == $argumentChild->getName()) {
                $updaters[uniqid()] = trim((string)$argumentChild);
            }
        }
        return $updaters;
    }

    /**
     * Schedule structural changes for move directive
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @throws Magento_Exception
     * @return Mage_Core_Model_Layout
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
            throw new Magento_Exception('Element name and destination must be specified.');
        }
        return $this;
    }

    /**
     * Populate queue for generating structural elements
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param Mage_Core_Model_Layout_Element $parent
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
            self::SCHEDULED_STRUCTURE_INDEX_NAME            => $node->getName(),
            self::SCHEDULED_STRUCTURE_INDEX_ALIAS           => '',
            self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME     => '',
            self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME    => null,
            self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER        => true,
            self::SCHEDULED_STRUCTURE_INDEX_LAYOUT_ELEMENT  => $node
        );

        $parentName = $parent->getElementName();
        if ($parentName) {
            $row[self::SCHEDULED_STRUCTURE_INDEX_ALIAS] = (string)$node->getAttribute('as');
            $row[self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME] = $parentName;

            list(
                $row[self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME],
                $row[self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER]
            ) = $this->_beforeAfterToSibling($node);

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
     * @param Mage_Core_Model_Layout_Element $node
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param string $key in _scheduledStructure represent element name
     */
    protected function _scheduleElement($key)
    {
        $row = $this->_scheduledStructure->getStructureElement($key);

        if (!isset($row[self::SCHEDULED_STRUCTURE_INDEX_LAYOUT_ELEMENT])) {
            Mage::log("Broken reference: missing declaration of the element '{$key}'.", Zend_Log::CRIT);
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
                $this->_structure->setAsChild($name, $parentName, $alias);
            } else {
                Mage::log("Broken reference: the '{$name}' element cannot be added as child to '{$parentName}, "
                        . 'because the latter doesn\'t exist', Zend_Log::CRIT
                );
            }
        }
        $this->_scheduledStructure->unsetStructureElement($key);
        $data = array(
            $type,
            $node,
            isset($row['actions']) ? $row['actions'] : array(),
            isset($row['arguments']) ? $row['arguments'] : array()
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
        $position = strpos($class, 'Block');
        $key = $position !== false ? substr($class, $position + 6) : $class;
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
     * @return Mage_Core_Block_Abstract
     * @throws Magento_Exception
     */
    protected function _generateBlock($elementName)
    {
        list($type, $node, $actions, $arguments) = $this->_scheduledStructure->getElement($elementName);
        if ($type !== self::TYPE_BLOCK) {
            throw new Magento_Exception("Unexpected element type specified for generating block: {$type}.");
        }

        // create block
        if (!empty($node['class'])) {
            $className = (string)$node['class'];
        } else {
            $className = (string)$node['type'];
        }

        $arguments = $this->_argumentProcessor->process($arguments);
        $dictionary = $this->_dataSourceFactory->getByNamespace((string)$node['name']);

        $block = $this->_createBlock($className, $elementName,
            array('data' => $arguments, 'dictionary' => $dictionary));

        if (!empty($node['module'])) {
            $block->setModuleName((string)$node['module']);
        }

        // TODO: is the instanceof check below the right thing to do?
        if (!empty($node['template'])) {
            $templateFileName = (string)$node['template'];
            if ($block instanceof Mage_Core_Block_Template) {
                $block->assign($dictionary);
            }
            $block->setTemplate($templateFileName);
        }

        $this->_scheduledStructure->unsetElement($elementName);

        // execute block methods
        foreach ($actions as $action) {
            list($actionNode, $parent) = $action;
            $this->_generateAction($actionNode, $parent);
        }

        return $block;
    }

    public function getDatasources()
    {
        return $this->_datasources;
    }

    /**
     * Set container-specific data to structure element
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @throws Magento_Exception if any of arguments are invalid
     */
    protected function _generateContainer($name, $label, array $options)
    {
        if (empty($label)) {
            throw new Magento_Exception('Container requires a label.');
        }
        $this->_structure->setAttribute($name, self::CONTAINER_OPT_LABEL, $label);
        unset($options[self::CONTAINER_OPT_LABEL]);
        unset($options['type']);
        if (empty($options[self::CONTAINER_OPT_HTML_TAG])
            && (!empty($options[self::CONTAINER_OPT_HTML_ID]) || !empty($options[self::CONTAINER_OPT_HTML_CLASS]))
        ) {
            throw new Magento_Exception('HTML ID or class will not have effect, if HTML tag is not specified.');
        }
        foreach ($options as $key => $value) {
            $this->_structure->setAttribute($name, $key, $value);
        }
    }

    /**
     * Run action defined in layout update
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param Mage_Core_Model_Layout_Element $parent
     */
    protected function _generateAction($node, $parent)
    {
        $configPath = $node->getAttribute('ifconfig');
        if ($configPath && !Mage::getStoreConfigFlag($configPath)) {
            return;
        }

        $method = $node->getAttribute('method');
        $parentName = $node->getAttribute('block');
        if (empty($parentName)) {
            $parentName = $parent->getElementName();
        }

        $profilerKey = 'BLOCK_ACTION:' . $parentName . '>' . $method;
        Magento_Profiler::start($profilerKey);

        $block = $this->getBlock($parentName);
        if (!empty($block)) {
            $args = $this->_extractArgs($node);
            $this->_translator->translateActionParameters($node, $args);
            call_user_func_array(array($block, $method), $args);
        }

        Magento_Profiler::stop($profilerKey);
    }

    /**
     * Get child block if exists
     *
     * @param string $parentName
     * @param string $alias
     * @return bool|Mage_Core_Block_Abstract
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
     * @return Mage_Core_Model_Layout
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
            $sibling = $this->_filterSearchMinus($offsetOrSibling, $children, $after);
            if ($childName !== $sibling) {
                $siblingParentName = $this->_structure->getParentId($sibling);
                if ($parentName !== $siblingParentName) {
                    Mage::log(
                        "Broken reference: the '{$childName}' tries to reorder itself towards '{$sibling}', "
                        . "but their parents are different: '{$parentName}' and '{$siblingParentName}' respectively.",
                        Zend_Log::CRIT
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
     * @return Mage_Core_Model_Layout
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
            if ($this->isBlock($name)) {
                $result = $this->_renderBlock($name);
            } else {
                $result = $this->_renderContainer($name);
            }
            $this->_renderElementCache[$name] = $result;
        }
        $this->_renderingOutput->setData('output', $this->_renderElementCache[$name]);
        Mage::dispatchEvent('core_layout_render_element', array(
            'element_name' => $name,
            'layout'       => $this,
            'transport'    => $this->_renderingOutput,
        ));
        return $this->_renderingOutput->getData('output');
    }

    /**
     * Gets HTML of block element
     *
     * @param string $name
     * @return string
     * @throws Magento_Exception
     */
    protected function _renderBlock($name)
    {
        $block = $this->getBlock($name);
        return $block ? $block->toHtml() : '';
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
        if ($html == '' || !$this->_structure->getAttribute($name, self::CONTAINER_OPT_HTML_TAG)) {
            return $html;
        }

        $htmlId = $this->_structure->getAttribute($name, self::CONTAINER_OPT_HTML_ID);
        if ($htmlId) {
            $htmlId = ' id="' . $htmlId . '"';
        }

        $htmlClass = $this->_structure->getAttribute($name, self::CONTAINER_OPT_HTML_CLASS);
        if ($htmlClass) {
            $htmlClass = ' class="'. $htmlClass . '"';
        }

        $htmlTag = $this->_structure->getAttribute($name, self::CONTAINER_OPT_HTML_TAG);

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
     * Update args according to its type
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return array
     */
    protected function _extractArgs($node)
    {
        $args = (array)$node->children();
        unset($args['@attributes']);

        foreach ($args as $key => $arg) {
            $matches = array();
            if (($arg instanceof Mage_Core_Model_Layout_Element)) {
                if (isset($arg['helper'])) {
                    $args[$key] = $this->_getArgsByHelper($arg);
                } else {
                    /**
                     * if there is no helper we hope that this is assoc array
                     */
                    $arr = $this->_getArgsFromAssoc($arg);
                    if (!empty($arr)) {
                        $args[$key] = $arr;
                    }
                }
            } else if (preg_match('/\{\{([a-zA-Z\.]*)\}\}/', $arg, $matches)) {
                $args[$key] = $this->_dataSourceFactory->getArgumentValue($matches[1]);
            }
        }

        if (isset($node['json'])) {
            $json = explode(' ', (string)$node['json']);
            foreach ($json as $arg) {
                $args[$arg] = Mage::helper('Mage_Core_Helper_Data')->jsonDecode($args[$arg]);
            }
        }

        return $args;
    }

    /**
     * Gets arguments using helper method
     *
     * @param Mage_Core_Model_Layout_Element $arg
     * @return mixed
     */
    protected function _getArgsByHelper(Mage_Core_Model_Layout_Element $arg)
    {
        $helper = (string)$arg['helper'];
        list($helperName, $helperMethod) = explode('::', $helper);
        $arg = $arg->asArray();
        unset($arg['@']);
        return call_user_func_array(array(Mage::helper($helperName), $helperMethod), $arg);
    }

    /**
     * Converts input array to arguments array
     *
     * @param array $array
     * @return array
     */
    protected function _getArgsFromAssoc($array)
    {
        $arr = array();
        foreach ($array as $key => $value) {
            $arr[(string)$key] = $value->asArray();
        }
        return $arr;
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
            return self::TYPE_BLOCK === $this->_structure->getAttribute($name, 'type');
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
            return self::TYPE_CONTAINER === $this->_structure->getAttribute($name, 'type');
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
     * @param Mage_Core_Block_Abstract $block
     * @return Mage_Core_Model_Layout
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
     * @return Mage_Core_Model_Layout
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
     * @return Mage_Core_Block_Abstract
     */
    public function createBlock($type, $name = '', array $attributes = array())
    {
        $name = $this->_createStructuralElement($name, self::TYPE_BLOCK, $type);
        $block = $this->_createBlock($type, $name, $attributes);
        return $block;
    }

    /**
     * Create block and add to layout
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @param string $name
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    protected function _createBlock($block, $name, array $attributes = array())
    {
        $block = $this->_getBlockInstance($block, $attributes);

        $block->setType(get_class($block));
        $block->setNameInLayout($name);
        $block->addData(isset($attributes['data']) ? $attributes['data'] : array());
        $block->setLayout($this);

        $this->_blocks[$name] = $block;
        Mage::dispatchEvent('core_layout_block_create_after', array('block' => $block));
        return $this->_blocks[$name];
    }

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @param string $name
     * @param string $parent
     * @param string $alias
     * @return Mage_Core_Block_Abstract
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '')
    {
        if (empty($name) && $block instanceof Mage_Core_Block_Abstract) {
            $name = $block->getNameInLayout();
        }
        $name = $this->_createStructuralElement(
            $name,
            self::TYPE_BLOCK,
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
     */
    public function addContainer($name, $label, array $options = array(), $parent = '', $alias = '')
    {
        $name = $this->_createStructuralElement($name, self::TYPE_CONTAINER, $alias);
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
     * @param string|Mage_Core_Block_Abstract $block
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    protected function _getBlockInstance($block, array $attributes = array())
    {
        if ($block && is_string($block)) {
            if (class_exists($block)) {
                $block = $this->_blockFactory->createBlock($block, $attributes);
            }
        }
        if (!$block instanceof Mage_Core_Block_Abstract) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Invalid block type: %s', $block));
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
     * @return Mage_Core_Block_Abstract|bool
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
     * @return Mage_Core_Model_Layout
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
     * @return Mage_Core_Model_Layout
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
     * @return Mage_Core_Block_Messages
     */
    public function getMessagesBlock()
    {
        $block = $this->getBlock('messages');
        if ($block) {
            return $block;
        }
        return $this->createBlock('Mage_Core_Block_Messages', 'messages');
    }

    /**
     * Get block singleton
     *
     * @param string $type
     * @return Mage_Core_Helper_Abstract
     */
    public function getBlockSingleton($type)
    {
        if (!isset($this->_helpers[$type])) {
            if (!$type) {
                Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Invalid block type: %s', $type));
            }

            $helper = Mage::getModel($type);
            if ($helper) {
                if ($helper instanceof Mage_Core_Block_Abstract) {
                    $helper->setLayout($this);
                }
                $this->_helpers[$type] = $helper;
            }
        }
        return $this->_helpers[$type];
    }

    /**
     * Retrieve helper object
     *
     * @param   string $name
     * @return  Mage_Core_Helper_Abstract
     */
    public function helper($name)
    {
        $helper = Mage::helper($name);
        if (!$helper) {
            return false;
        }
        return $helper->setLayout($this);
    }

    /**
     * Lookup module name for translation from current specified layout node
     *
     * Priorities:
     * 1) "module" attribute in the element
     * 2) "module" attribute in any ancestor element
     * 3) layout handle name - first 1 or 2 parts (namespace is determined automatically)
     *
     * @param Varien_Simplexml_Element $node
     * @return string
     */
    public static function findTranslationModuleName(Varien_Simplexml_Element $node)
    {
        // Commented out code uses not yet implemented functionality.
        $result = (string) $node->getAttribute('module');
        if ($result) {
            //return Mage::getConfig()->getModuleConfig($result) ? $result : 'core';
            return $result;
        }
        foreach (array_reverse($node->xpath('ancestor::*[@module]')) as $element) {
            $result = (string)$element->getAttribute('module');
            if ($result) {
                //return Mage::getConfig()->getModuleConfig($result) ? $result : 'core';
                return $result;
            }
        }
        foreach ($node->xpath('ancestor-or-self::*[last()-1]') as $handle) {
            $name = Mage::getConfig()->determineOmittedNamespace($handle->getName(), true);
            if ($name) {
                //return Mage::getConfig()->getModuleConfig($name) ? $name : 'core';
                return $name;
            }
        }
        return 'Mage_Core';
    }

    /**
     * Retrieve block factory
     *
     * @return Mage_Core_Model_BlockFactory
     */
    public function getBlockFactory()
    {
        return $this->_blockFactory;
    }
}
