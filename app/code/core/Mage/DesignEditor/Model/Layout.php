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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Model for manipulating layout for purpose of design editor
 */
class Mage_DesignEditor_Model_Layout extends Mage_Core_Model_Layout
{
    /**
     * Flag that keeps true in case when we need to sanitize layout blocks
     *
     * @var bool
     */
    protected $_sanitationEnabled = false;

    /**
     * Is block wrapping enabled flag
     *
     * @var bool
     */
    protected $_wrappingEnabled = false;

    /**
     * List of block types considered as "safe"
     *
     * "Safe" means that they will work with any template (if applicable)
     *
     * @var array|null
     */
    protected $_blockWhiteList = null;

    /**
     * List of block types considered as "not safe"
     *
     * @var array|null
     */
    protected $_blockBlackList = null;

    /**
     * List of layout containers that potentially have "safe" blocks
     *
     * @var array|null
     */
    protected $_containerWhiteList = null;

    /**
     * Block that wrap page elements when wrapping enabled
     *
     * @var Mage_DesignEditor_Block_Template
     */
    protected $_wrapperBlock;

    /**
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_helper;

    /**
     * List of JS events not allowed in VDE mode
     *
     * @var array
     */
    protected $_jsEvents = array(
        'onclick',
        'onblur',
        'ondblclick',
        'onfocus',
        'onkeydown',
        'onkeypress',
        'onkeyup',
        'onmousedown',
        'onmousemove',
        'onmouseout',
        'onmouseover',
        'onmouseup'
    );

    /**
     * @param Mage_Core_Model_BlockFactory $blockFactory
     * @param Magento_Data_Structure $structure
     * @param Mage_Core_Model_Layout_Argument_Processor $argumentProcessor
     * @param Mage_Core_Model_Layout_Translator $translator
     * @param Mage_Core_Model_Layout_ScheduledStructure $scheduledStructure
     * @param Mage_DesignEditor_Block_Template $wrapperBlock
     * @param Mage_DesignEditor_Helper_Data $helper
     * @param string $area
     */
    public function __construct(
        Mage_Core_Model_BlockFactory $blockFactory,
        Magento_Data_Structure $structure,
        Mage_Core_Model_Layout_Argument_Processor $argumentProcessor,
        Mage_Core_Model_Layout_Translator $translator,
        Mage_Core_Model_Layout_ScheduledStructure $scheduledStructure,
        Mage_DesignEditor_Block_Template $wrapperBlock,
        Mage_DesignEditor_Helper_Data $helper,
        $area = Mage_Core_Model_Design_Package::DEFAULT_AREA
    ) {
        $this->_wrapperBlock = $wrapperBlock;
        $this->_helper       = $helper;
        parent::__construct($blockFactory, $structure, $argumentProcessor, $translator, $scheduledStructure, $area);
    }

    /**
     * Set sanitizing flag
     *
     * @param bool $flag
     */
    public function setSanitizing($flag)
    {
        $this->_sanitationEnabled = $flag;
    }

    /**
     * Set wrapping flag
     *
     * @param bool $flag
     */
    public function setWrapping($flag)
    {
        $this->_wrappingEnabled = $flag;
    }

    /**
     * Replace all inline JavaScript
     *
     * @return string
     */
    public function getOutput()
    {
        $output = parent::getOutput();
        if (preg_match('/<body\s*[^>]*>.*<\/body>/is', $output, $body)) {
            $oldBody = $body[0];
            // Replace script tags
            $newBody = preg_replace('/<script\s*[^>]*>.*?<\/script>/is', '', $oldBody);
            // Replace JS events
            foreach ($this->_jsEvents as $event) {
                $newBody = preg_replace("/(<[^>]+){$event}\\s*=\\s*(['\"])/is", "$1{$event}-vde=$2", $newBody);
            }
            // Replace href JS
            $newBody = preg_replace('/(<[^>]+)href\s*=\s*([\'"])javascript:/is', '$1href-vde=$2', $newBody);
            $output = str_replace($oldBody, $newBody, $output);
        }
        return $output;
    }

    /**
     * Replace all potentially dangerous blocks in layout into stubs
     *
     * It is important to sanitize the references first, because they refer to blocks to check whether they are safe.
     * But if the blocks were sanitized before references, then they ALL will be considered safe.
     *
     * @param Varien_Simplexml_Element $node
     */
    public function sanitizeLayout(Varien_Simplexml_Element $node)
    {
        $this->_sanitizeLayout($node, 'reference'); // it is important to sanitize references first
        $this->_sanitizeLayout($node, 'block');
    }

    /**
     * Sanitize nodes which names match the specified one
     *
     * Recursively goes through all underlying nodes
     *
     * @param Varien_Simplexml_Element $node
     * @param string $nodeName
     */
    protected function _sanitizeLayout(Varien_Simplexml_Element $node, $nodeName)
    {
        if ($node->getName() == $nodeName) {
            switch ($nodeName) {
                case 'block':
                    $this->_sanitizeBlock($node);
                    break;
                case 'reference':
                    $this->_sanitizeReference($node);
                    break;
            }
        }
        foreach ($node->children() as $child) {
            $this->_sanitizeLayout($child, $nodeName);
        }
    }

    /**
     * Replace "unsafe" types of blocks into Mage_Core_Block_Template and cut all their actions
     *
     * A "stub" template will be assigned for the blocks
     *
     * @param Varien_Simplexml_Element $node
     */
    protected function _sanitizeBlock(Varien_Simplexml_Element $node)
    {
        $type = $node->getAttribute('type');
        if (!$type) {
            return; // we encountered a node with name "block", however it doesn't actually define any block...
        }
        if ($this->_isParentSafe($node) || $this->_isTypeSafe($type)) {
            return;
        }
        $this->_overrideAttribute($node, 'template', 'Mage_DesignEditor::stub.phtml');
        $this->_overrideAttribute($node, 'type', 'Mage_Core_Block_Template');
        $this->_deleteNodes($node, 'action');
    }

    /**
     * Get list of allowed containers
     *
     * @return array
     */
    protected function _getContainerWhiteList()
    {
        if ($this->_containerWhiteList === null) {
            $this->_containerWhiteList = $this->_helper->getContainerWhiteList();
        }
        return $this->_containerWhiteList;
    }

    /**
     * Whether parent node of specified node can be considered a safe container
     *
     * @param Varien_Simplexml_Element $node
     * @return bool
     */
    protected function _isParentSafe(Varien_Simplexml_Element $node)
    {
        $parentAttributes = $node->getParent()->attributes();
        if (isset($parentAttributes['name'])) {
            if (!in_array($parentAttributes['name'], $this->_getContainerWhiteList())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get list of allowed blocks
     *
     * @return array
     */
    protected function _getBlockWhiteList()
    {
        if ($this->_blockWhiteList === null) {
            $this->_blockWhiteList = $this->_helper->getBlockWhiteList();
        }
        return $this->_blockWhiteList;
    }

    /**
     * Get list of not allowed blocks
     *
     * @return array
     */
    protected function _getBlockBlackList()
    {
        if ($this->_blockBlackList === null) {
            $this->_blockBlackList = $this->_helper->getBlockBlackList();
        }
        return $this->_blockBlackList;
    }

    /**
     * Check whether the specified type of block can be safely used in layout without required context
     *
     * @param string $type
     * @return bool
     */
    protected function _isTypeSafe($type)
    {
        if (in_array($type, $this->_getBlockBlackList())) {
            return false;
        }
        foreach ($this->_getBlockWhiteList() as $safeType) {
            if ('_' !== substr($safeType, -1, 1)) {
                if ($type === $safeType) {
                    return true;
                }
            } elseif (0 === strpos($type, $safeType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add or update specified attribute of a node with specified value
     *
     * @param Varien_Simplexml_Element $node
     * @param string $name
     * @param string $value
     */
    protected function _overrideAttribute(Varien_Simplexml_Element $node, $name, $value)
    {
        $attributes = $node->attributes();
        if (isset($attributes[$name])) {
            $attributes[$name] = $value;
        } else {
            $attributes->addAttribute($name, $value);
        }
    }

    /**
     * Delete child nodes by specified name
     *
     * @param Varien_Simplexml_Element $node
     * @param string $name
     */
    protected function _deleteNodes(Varien_Simplexml_Element $node, $name)
    {
        $count = count($node->{$name});
        for ($i = $count; $i >= 0; $i--) {
            unset($node->{$name}[$i]);
        }
    }

    /**
     * Cleanup reference node according to the block it refers to
     *
     * Look for the block by reference name and if the block is "unsafe", cleanup the reference node from actions
     *
     * @param Varien_Simplexml_Element $node
     */
    protected function _sanitizeReference(Varien_Simplexml_Element $node)
    {
        $attributes = $node->attributes();
        $name = $attributes['name'];
        $result = $node->xpath("//block[@name='{$name}']") ?: array();
        /** @var $block Varien_Simplexml_Element */
        foreach ($result as $block) {
            $isTypeSafe = $this->_isTypeSafe($block->getAttribute('type'));
            if (!$isTypeSafe || !$this->_isParentSafe($block)) {
                $this->_deleteNodes($node, 'action');
            }
            break;
        }
    }

    /**
     * Create structure of elements from the loaded XML configuration
     */
    public function generateElements()
    {
        if ($this->_sanitationEnabled) {
            $this->sanitizeLayout($this->getNode());
        }

        parent::generateElements();
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
        $result = parent::_renderBlock($name);

        if ($this->_wrappingEnabled) {
            $block = $this->getBlock($name);
            if (strpos(get_class($block), 'Mage_DesignEditor_Block_') !== 0 && $this->isManipulationAllowed($name)) {
                $result = $this->_wrapElement($result, $name, false, true);
            }
        }

        return $result;
    }

    /**
     * Gets HTML of container element
     *
     * @param string $name
     * @return string
     */
    protected function _renderContainer($name)
    {
        $result = parent::_renderContainer($name);

        if ($this->_wrappingEnabled && $this->hasElement($name)) {
            $result = $this->_wrapElement($result, $name, true);
        }

        return $result;
    }

    /**
     * Wrap layout element
     *
     * @param string $elementContent
     * @param string $elementName
     * @param bool $isContainer
     * @param bool $canManipulate
     * @return string
     */
    protected function _wrapElement($elementContent, $elementName, $isContainer = false, $canManipulate = false)
    {
        $elementId = 'vde_element_' . rtrim(strtr(base64_encode($elementName), '+/', '-_'), '=');
        $this->_wrapperBlock->setData(array(
            'element_id'              => $elementId,
            'element_title'           => $this->getElementProperty($elementName, 'label') ?: $elementName,
            'element_html'            => $elementContent,
            'is_manipulation_allowed' => $canManipulate,
            'is_container'            => $isContainer,
            'element_name'            => $elementName,
        ));
        return $this->_wrapperBlock->toHtml();
    }
}
