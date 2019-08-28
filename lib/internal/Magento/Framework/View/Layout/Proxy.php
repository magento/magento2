<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Proxy class for @see \Magento\Framework\View\Layout
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Proxy extends \Magento\Framework\View\Layout implements \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\View\Layout
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\View\Layout::class,
        $shared = true
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * Sleep magic method.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['_subject', '_isShared'];
    }

    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\View\Layout
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_isShared
                ? $this->_objectManager->get($this->_instanceName)
                : $this->_objectManager->create($this->_instanceName);
        }
        return $this->_subject;
    }

    /**
     * @inheritDoc
     */
    public function setGeneratorPool(\Magento\Framework\View\Layout\GeneratorPool $generatorPool)
    {
        return $this->_getSubject()->setGeneratorPool($generatorPool);
    }

    /**
     * @inheritDoc
     */
    public function setBuilder(\Magento\Framework\View\Layout\BuilderInterface $builder)
    {
        return $this->_getSubject()->setBuilder($builder);
    }

    /**
     * @inheritDoc
     */
    public function publicBuild()
    {
        $this->_getSubject()->publicBuild();
    }

    /**
     * @inheritDoc
     */
    public function getUpdate()
    {
        return $this->_getSubject()->getUpdate();
    }

    /**
     * @inheritDoc
     */
    public function generateXml()
    {
        return $this->_getSubject()->generateXml();
    }

    /**
     * @inheritDoc
     */
    public function generateElements()
    {
        $this->_getSubject()->generateElements();
    }

    /**
     * @inheritDoc
     */
    public function getChildBlock($parentName, $alias)
    {
        return $this->_getSubject()->getChildBlock($parentName, $alias);
    }

    /**
     * @inheritDoc
     */
    public function setChild($parentName, $elementName, $alias)
    {
        return $this->_getSubject()->setChild($parentName, $elementName, $alias);
    }

    /**
     * @inheritDoc
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true)
    {
        $this->_getSubject()->reorderChild($parentName, $childName, $offsetOrSibling, $after);
    }

    /**
     * @inheritDoc
     */
    public function unsetChild($parentName, $alias)
    {
        return $this->_getSubject()->unsetChild($parentName, $alias);
    }

    /**
     * @inheritDoc
     */
    public function getChildNames($parentName)
    {
        return $this->_getSubject()->getChildNames($parentName);
    }

    /**
     * @inheritDoc
     */
    public function getChildBlocks($parentName)
    {
        return $this->_getSubject()->getChildBlocks($parentName);
    }

    /**
     * @inheritDoc
     */
    public function getChildName($parentName, $alias)
    {
        return $this->_getSubject()->getChildName($parentName, $alias);
    }

    /**
     * @inheritDoc
     */
    public function renderElement($name, $useCache = true)
    {
        return $this->_getSubject()->renderElement($name, $useCache);
    }

    /**
     * @inheritDoc
     */
    public function renderNonCachedElement($name)
    {
        return $this->_getSubject()->renderNonCachedElement($name);
    }

    /**
     * @inheritDoc
     */
    public function addToParentGroup($blockName, $parentGroupName)
    {
        return $this->_getSubject()->addToParentGroup($blockName, $parentGroupName);
    }

    /**
     * @inheritDoc
     */
    public function getGroupChildNames($blockName, $groupName)
    {
        return $this->_getSubject()->getGroupChildNames($blockName, $groupName);
    }

    /**
     * @inheritDoc
     */
    public function hasElement($name)
    {
        return $this->_getSubject()->hasElement($name);
    }

    /**
     * @inheritDoc
     */
    public function getElementProperty($name, $attribute)
    {
        return $this->_getSubject()->getElementProperty($name, $attribute);
    }

    /**
     * @inheritDoc
     */
    public function isBlock($name)
    {
        return $this->_getSubject()->isBlock($name);
    }

    /**
     * @inheritDoc
     */
    public function isUiComponent($name)
    {
        return $this->_getSubject()->isUiComponent($name);
    }

    /**
     * @inheritDoc
     */
    public function isContainer($name)
    {
        return $this->_getSubject()->isContainer($name);
    }

    /**
     * @inheritDoc
     */
    public function isManipulationAllowed($name)
    {
        return $this->_getSubject()->isManipulationAllowed($name);
    }

    /**
     * @inheritDoc
     */
    public function setBlock($name, $block)
    {
        return $this->_getSubject()->setBlock($name, $block);
    }

    /**
     * @inheritDoc
     */
    public function unsetElement($name)
    {
        return $this->_getSubject()->unsetElement($name);
    }

    /**
     * @inheritDoc
     */
    public function createBlock($type, $name = '', array $arguments = [])
    {
        return $this->_getSubject()->createBlock($type, $name, $arguments);
    }

    /**
     * @inheritDoc
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '')
    {
        return $this->_getSubject()->addBlock($block, $name, $parent, $alias);
    }

    /**
     * @inheritDoc
     */
    public function addContainer($name, $label, array $options = [], $parent = '', $alias = '')
    {
        $this->_getSubject()->addContainer($name, $label, $options, $parent, $alias);
    }

    /**
     * @inheritDoc
     */
    public function renameElement($oldName, $newName)
    {
        return $this->_getSubject()->renameElement($oldName, $newName);
    }

    /**
     * @inheritDoc
     */
    public function getAllBlocks()
    {
        return $this->_getSubject()->getAllBlocks();
    }

    /**
     * @inheritDoc
     */
    public function getBlock($name)
    {
        return $this->_getSubject()->getBlock($name);
    }

    /**
     * @inheritDoc
     */
    public function getUiComponent($name)
    {
        return $this->_getSubject()->getUiComponent($name);
    }

    /**
     * @inheritDoc
     */
    public function getParentName($childName)
    {
        return $this->_getSubject()->getParentName($childName);
    }

    /**
     * @inheritDoc
     */
    public function getElementAlias($name)
    {
        return $this->_getSubject()->getElementAlias($name);
    }

    /**
     * @inheritDoc
     */
    public function addOutputElement($name)
    {
        return $this->_getSubject()->addOutputElement($name);
    }

    /**
     * @inheritDoc
     */
    public function removeOutputElement($name)
    {
        return $this->_getSubject()->removeOutputElement($name);
    }

    /**
     * @inheritDoc
     */
    public function getOutput()
    {
        return $this->_getSubject()->getOutput();
    }

    /**
     * @inheritDoc
     */
    public function getMessagesBlock()
    {
        return $this->_getSubject()->getMessagesBlock();
    }

    /**
     * @inheritDoc
     */
    public function getBlockSingleton($type)
    {
        return $this->_getSubject()->getBlockSingleton($type);
    }

    /**
     * @inheritDoc
     */
    public function addAdjustableRenderer($namespace, $staticType, $dynamicType, $type, $template, $data = [])
    {
        return $this->_getSubject()->addAdjustableRenderer(
            $namespace,
            $staticType,
            $dynamicType,
            $type,
            $template,
            $data
        );
    }

    /**
     * @inheritDoc
     */
    public function getRendererOptions($namespace, $staticType, $dynamicType)
    {
        return $this->_getSubject()->getRendererOptions($namespace, $staticType, $dynamicType);
    }

    /**
     * @inheritDoc
     */
    public function executeRenderer($namespace, $staticType, $dynamicType, $data = [])
    {
        $this->_getSubject()->executeRenderer($namespace, $staticType, $dynamicType, $data);
    }

    /**
     * @inheritDoc
     */
    public function initMessages($messageGroups = [])
    {
        $this->_getSubject()->initMessages($messageGroups);
    }

    /**
     * @inheritDoc
     */
    public function isCacheable()
    {
        return $this->_getSubject()->isCacheable();
    }

    /**
     * @inheritDoc
     */
    public function isPrivate()
    {
        return $this->_getSubject()->isPrivate();
    }

    /**
     * @inheritDoc
     */
    public function setIsPrivate($isPrivate = true)
    {
        return $this->_getSubject()->setIsPrivate($isPrivate);
    }

    /**
     * @inheritDoc
     */
    public function getReaderContext()
    {
        return $this->_getSubject()->getReaderContext();
    }

    /**
     * @inheritDoc
     */
    public function setXml(\Magento\Framework\Simplexml\Element $node)
    {
        return $this->_getSubject()->setXml($node);
    }

    /**
     * @inheritDoc
     */
    public function getNode($path = null)
    {
        return $this->_getSubject()->getNode($path);
    }

    /**
     * @inheritDoc
     */
    public function getXpath($xpath)
    {
        return $this->_getSubject()->getXpath($xpath);
    }

    /**
     * @inheritDoc
     */
    public function getXmlString()
    {
        return $this->_getSubject()->getXmlString();
    }

    /**
     * @inheritDoc
     */
    public function loadFile($filePath)
    {
        return $this->_getSubject()->loadFile($filePath);
    }

    /**
     * @inheritDoc
     */
    public function loadString($string)
    {
        return $this->_getSubject()->loadString($string);
    }

    /**
     * @inheritDoc
     */
    public function loadDom(\DOMNode $dom)
    {
        return $this->_getSubject()->loadDom($dom);
    }

    /**
     * @inheritDoc
     */
    public function setNode($path, $value, $overwrite = true)
    {
        return $this->_getSubject()->setNode($path, $value, $overwrite);
    }

    /**
     * @inheritDoc
     */
    public function applyExtends()
    {
        return $this->_getSubject()->applyExtends();
    }

    /**
     * @inheritDoc
     */
    public function processFileData($text)
    {
        return $this->_getSubject()->processFileData($text);
    }

    /**
     * @inheritDoc
     */
    public function extend(\Magento\Framework\Simplexml\Config $config, $overwrite = true)
    {
        return $this->_getSubject()->extend($config, $overwrite);
    }
}
