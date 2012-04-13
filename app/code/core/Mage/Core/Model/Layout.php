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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Layout model
 *
 * @category   Mage
 * @package    Mage_Core
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Core_Model_Layout extends Varien_Simplexml_Config
{
    /**
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG   = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID    = 'htmlId';
    const CONTAINER_OPT_LABEL      = 'label';

    /**
     * Layout Update module
     *
     * @var Mage_Core_Model_Layout_Update
     */
    protected $_update;

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
     * Available options for containers in layout
     *
     * @var array
     */
    protected $_containerOptions = array(
        self::CONTAINER_OPT_HTML_CLASS,
        self::CONTAINER_OPT_HTML_ID,
        self::CONTAINER_OPT_HTML_TAG,
        self::CONTAINER_OPT_LABEL,
    );

    /**
     * Cache of generated elements' HTML
     *
     * @var array
     */
    protected $_renderElementCache = array();

    /**
     * Layout structure model
     *
     * @var Mage_Core_Model_Layout_Structure
     */
    protected $_structure;

    /**
     * Class constructor
     *
     * @param array $arguments
     */
    public function __construct(array $arguments = array())
    {
        $this->_area = isset($arguments['area']) ? $arguments['area'] : Mage_Core_Model_Design_Package::DEFAULT_AREA;
        if (isset($arguments['structure'])) {
            if ($arguments['structure'] instanceof Mage_Core_Model_Layout_Structure) {
                $this->_structure = $arguments['structure'];
            } else {
                throw new InvalidArgumentException('Expected instance of Mage_Core_Model_Layout_Structure.');
            }
        } else {
            $this->_structure = Mage::getModel('Mage_Core_Model_Layout_Structure');
        }
        $this->_elementClass = Mage::getConfig()->getModelClassName('Mage_Core_Model_Layout_Element');
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        $this->_renderingOutput = new Varien_Object;
    }

    /**
     * Retrieve the layout update instance
     *
     * @return Mage_Core_Model_Layout_Update
     */
    public function getUpdate()
    {
        if (!$this->_update) {
            $this->_update = Mage::getModel('Mage_Core_Model_Layout_Update', array('area' => $this->getArea()));
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
        $removeInstructions = (array)$xml->xpath("//remove[@name]");
        foreach ($removeInstructions as $infoNode) {
            $attributes = $infoNode->attributes();
            $blockName = (string)$attributes->name;
            $xpath = "//block[@name='" . $blockName . "']"
                . " | //reference[@name='" . $blockName . "']"
                . " | //action[(@method='insert' or @method='append') and *[position()=1 and text()='$blockName']]";
            $ignoreNodes = $xml->xpath($xpath);
            if (!$ignoreNodes) {
                continue;
            }

            foreach ($ignoreNodes as $block) {
                $acl = (string)$attributes->acl;
                if ($block->getAttribute('ignore') !== null || ($acl
                    && Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed($acl))) {
                    continue;
                }
                if (!isset($block->attributes()->ignore)) {
                    $block->addAttribute('ignore', true);
                }
            }
        }
        $this->setXml($xml);
        return $this;
    }

    /**
     * Create layout blocks hierarchy from layout xml configuration
     */
    public function generateBlocks()
    {
        $this->_generateBlocks($this->getNode());
        $this->_structure->sortElements();
    }

    /**
     * Recursive function, that goes through whole layout and create layout blocks hierarchy from xml configuration
     *
     * @param Mage_Core_Model_Layout_Element $parent
     */
    protected function _generateBlocks($parent)
    {
        /** @var Mage_Core_Model_Layout_Element $node  */
        foreach ($parent as $node) {
            $attributes = $node->attributes();
            if ((bool)$attributes->ignore) {
                continue;
            }
            switch ($node->getName()) {
                case 'container':
                case 'block':
                    $this->_generateElement($node, $parent);
                    $this->_generateBlocks($node);
                    break;

                case 'reference':
                    $this->_generateBlocks($node);
                    break;

                case 'action':
                    $this->_generateAction($node, $parent);
                    break;
            }
        }
    }

    /**
     * Creates block/container object based on xml node data
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param Mage_Core_Model_Layout_Element $parent
     * @return Mage_Core_Model_Layout
     * @throws Magento_Exception
     */
    protected function _generateElement($node, $parent)
    {
        $elementType = $node->getName();
        $name = $node->getAttribute('name');

        $profilerKey = strtoupper($elementType) . ':' . $name;
        Magento_Profiler::start($profilerKey);

        $parentName = $node->getAttribute('parent');
        if (is_null($parentName)) {
            $parentName = $parent->getElementName();
        }

        $alias = $node->getAttribute('as');
        if (!$alias) {
            $alias = $name;
        }

        if (isset($node['after'])) {
            $sibling = $node['after'];
            $after = true;
        } else if (isset($node['before'])) {
            $sibling = $node['before'];
            $after = false;
        } else {
            $sibling = null;
            $after = true;
        }

        $options = $this->_extractContainerOptions($node);
        $elementName = $this->_structure
            ->insertElement($parentName, $name, $elementType, $alias, $sibling, $after, $options);

        if ($this->_structure->isBlock($elementName)) {
            $node['name'] = $elementName;
            $this->_generateBlock($node);
        } else {
            $this->_removeBlock($name);
        }

        if (isset($node['output'])) {
            $this->addOutputElement($elementName);
        }

        Magento_Profiler::stop($profilerKey);

        return $this;
    }

    /**
     * Remove block from blocks list
     *
     * @param string $name
     * @return Mage_Core_Model_Layout
     */
    protected function _removeBlock($name)
    {
        if (isset($this->_blocks[$name])) {
            unset($this->_blocks[$name]);
        }
        return $this;
    }

    /**
     * Extract appropriate options from a node if it is a container
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return array
     */
    protected function _extractContainerOptions(Mage_Core_Model_Layout_Element $node)
    {
        $options = array();
        if ('container' == $node->getName()) {
            foreach ($this->_containerOptions as $optName) {
                if ($value = $node->getAttribute($optName)) {
                    $options[$optName] = $value;
                }
            }
        }

        return $options;
    }

    /**
     * Creates block object based on xml node data and add it to the layout
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @return Mage_Core_Block_Abstract
     */
    protected function _generateBlock(Mage_Core_Model_Layout_Element $node)
    {
        if (!empty($node['class'])) {
            $className = (string)$node['class'];
        } else {
            $className = (string)$node['type'];
        }
        $elementName = $node->getAttribute('name');

        $block = $this->_createBlock($className, $elementName);
        if (!empty($node['template'])) {
            $block->setTemplate((string)$node['template']);
        }

        return $block;
    }

    /**
     * Run action defined in layout update
     *
     * @param Mage_Core_Model_Layout_Element $node
     * @param Mage_Core_Model_Layout_Element $parent
     * @return Mage_Core_Model_Layout
     * @throws Magento_Exception
     */
    protected function _generateAction($node, $parent)
    {
        $configPath = $node->getAttribute('ifconfig');
        if ($configPath && !Mage::getStoreConfigFlag($configPath)) {
            return $this;
        }

        if (Mage_Core_Model_Layout_Structure::ELEMENT_TYPE_CONTAINER === $parent->getName()) {
            throw new Magento_Exception('Action can not be placed inside container');
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

            $this->_translateLayoutNode($node, $args);
            call_user_func_array(array($block, $method), $args);
        }

        Magento_Profiler::stop($profilerKey);

        return $this;
    }

    /**
     * Insert block into layout structure
     *
     * @param string $parentName
     * @param string $name
     * @param string $alias
     * @param string|null $sibling
     * @param bool $after
     * @return bool|string
     */
    public function insertBlock($parentName, $name, $alias = '', $sibling = null, $after = true)
    {
        return $this->_structure->insertBlock($parentName, $name, $alias, $sibling, $after);
    }

    /**
     * Insert container into layout structure
     *
     * @param string $parentName
     * @param string $name
     * @param string $alias
     * @param string|null $sibling
     * @param bool $after
     * @return bool|string
     */
    public function insertContainer($parentName, $name, $alias = '', $sibling = null, $after = true)
    {
        return $this->_structure->insertContainer($parentName, $name, $alias, $sibling, $after);
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
        $name = $this->_structure->getChildName($parentName, $alias);
        if ($this->_structure->isBlock($name)) {
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
        $this->_structure->setChild($parentName, $elementName, $alias);
        return $this;
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
        return $this->_structure->getChildNames($parentName);
    }

    /**
     * Get list of child blocks
     *
     * @param string $parentName
     * @return array
     */
    public function getChildBlocks($parentName)
    {
        $blocks = array();
        foreach ($this->getChildNames($parentName) as $name) {
            $block = $this->getBlock($name);
            if ($block) {
                $blocks[] = $block;
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
        return $this->_structure->getChildName($parentName, $alias);
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
            if ($this->_structure->isBlock($name)) {
                $result = $this->_renderBlock($name);
            } else {
                $result = $this->_renderContainer($name);
            }
            $this->_renderElementCache[$name] = $result;
        }
        $this->_renderingOutput->setData('output', $this->_renderElementCache[$name]);
        Mage::dispatchEvent('core_layout_render_element', array(
            'element_name' => $name,
            'structure'    => $this->_structure,
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
        $children = $this->_structure->getChildNames($name);
        foreach ($children as $child) {
            $html .= $this->renderElement($child);
        }
        if ($html == '' || !$this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_TAG)) {
            return $html;
        }

        $htmlId = $this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_ID);
        if ($htmlId) {
            $htmlId = ' id="' . $htmlId . '"';
        }

        $htmlClass = $this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_CLASS);
        if ($htmlClass) {
            $htmlClass = ' class="'. $htmlClass . '"';
        }

        $htmlTag = $this->_structure->getElementAttribute($name, self::CONTAINER_OPT_HTML_TAG);

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
     * Checks if element with specified name is container
     *
     * @param string $name
     * @return bool
     */
    public function isContainer($name)
    {
        return $this->_structure->isContainer($name);
    }

    /**
     * Translate layout node
     *
     * @param Varien_Simplexml_Element $node
     * @param array $args
     **/
    protected function _translateLayoutNode($node, &$args)
    {
        if (isset($node['translate'])) {
            // Translate value by core module if module attribute was not set
            $moduleName = (isset($node['module'])) ? (string)$node['module'] : 'Mage_Core';

            // Handle translations in arrays if needed
            $translatableArgs = explode(' ', (string)$node['translate']);
            foreach ($translatableArgs as $translatableArg) {
                /*
                 * .(dot) character is used as a path separator in nodes hierarchy
                 * e.g. info.title means that Magento needs to translate value of <title> node
                 * that is a child of <info> node
                 */
                // @var $argumentHierarhy array - path to translatable item in $args array
                $argumentHierarchy = explode('.', $translatableArg);
                $argumentStack = &$args;
                $canTranslate = true;
                while (is_array($argumentStack) && count($argumentStack) > 0) {
                    $argumentName = array_shift($argumentHierarchy);
                    if (isset($argumentStack[$argumentName])) {
                        /*
                         * Move to the next element in arguments hieracrhy
                         * in order to find target translatable argument
                         */
                        $argumentStack = &$argumentStack[$argumentName];
                    } else {
                        // Target argument cannot be found
                        $canTranslate = false;
                        break;
                    }
                }
                if ($canTranslate && is_string($argumentStack)) {
                    // $argumentStack is now a reference to target translatable argument so it can be translated
                    $argumentStack = Mage::helper($moduleName)->__($argumentStack);
                }
            }
        }
    }

    /**
     * Save block in blocks registry
     *
     * @param string $name
     * @param Mage_Core_Block_abstract $block
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
        $name = $this->_structure->insertBlock('', $name);
        return $this->_createBlock($type, $name, $attributes);
    }

    /**
     * Create block and add to layout
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @param string $name
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    protected function _createBlock($block, $name='', array $attributes = array())
    {
        $block = $this->_getBlockInstance($block, $attributes);

        $block->setType(get_class($block));
        $block->setNameInLayout($name);
        $block->addData($attributes);
        $block->setLayout($this);

        $this->_blocks[$name] = $block;
        Mage::dispatchEvent('core_layout_block_create_after', array('block'=>$block));
        return $this->_blocks[$name];
    }

    /**
     * Add a block to registry, create new object if needed
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @param string $name
     * @param string $parent
     * @param string $alias
     * @param bool $after
     * @param string $sibling
     * @return Mage_Core_Block_Abstract
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '', $sibling = null, $after = true)
    {
        if (empty($name) && $block instanceof Mage_Core_Block_Abstract) {
            $name = $block->getNameInLayout();
        }
        $name = $this->_structure->insertBlock($parent, $name, $alias, $sibling, $after);
        return $this->_createBlock($block, $name);
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
    protected function _getBlockInstance($block, array $attributes=array())
    {
        if ($block && is_string($block)) {
            $block = Mage::getConfig()->getBlockClassName($block);
            if (Magento_Autoload::getInstance()->classExists($block)) {
                $block = new $block($attributes);
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
        return $this->_structure->getParentName($childName);
    }

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return string
     */
    public function getElementAlias($name)
    {
        return $this->_structure->getElementAlias($name);
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
     * Enter description here...
     *
     * @param string $type
     * @return Mage_Core_Helper_Abstract
     */
    public function getBlockSingleton($type)
    {
        if (!isset($this->_helpers[$type])) {
            $className = Mage::getConfig()->getBlockClassName($type);
            if (!$className) {
                Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Invalid block type: %s', $type));
            }

            $helper = new $className();
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
            $result = (string) $element->getAttribute('module');
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
}
