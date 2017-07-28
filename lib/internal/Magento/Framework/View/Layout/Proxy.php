<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Proxy class for @see \Magento\Framework\View\Layout
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 2.0.0
 */
class Proxy extends \Magento\Framework\View\Layout implements \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\View\Layout
     * @since 2.0.0
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     * @since 2.0.0
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
     * @return array
     * @since 2.0.0
     */
    public function __sleep()
    {
        return ['_subject', '_isShared'];
    }

    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     * @since 2.0.0
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     * @since 2.0.0
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\View\Layout
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGeneratorPool(\Magento\Framework\View\Layout\GeneratorPool $generatorPool)
    {
        return $this->_getSubject()->setGeneratorPool($generatorPool);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBuilder(\Magento\Framework\View\Layout\BuilderInterface $builder)
    {
        return $this->_getSubject()->setBuilder($builder);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function publicBuild()
    {
        $this->_getSubject()->publicBuild();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUpdate()
    {
        return $this->_getSubject()->getUpdate();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function generateXml()
    {
        return $this->_getSubject()->generateXml();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function generateElements()
    {
        $this->_getSubject()->generateElements();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getChildBlock($parentName, $alias)
    {
        return $this->_getSubject()->getChildBlock($parentName, $alias);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setChild($parentName, $elementName, $alias)
    {
        return $this->_getSubject()->setChild($parentName, $elementName, $alias);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true)
    {
        $this->_getSubject()->reorderChild($parentName, $childName, $offsetOrSibling, $after);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function unsetChild($parentName, $alias)
    {
        return $this->_getSubject()->unsetChild($parentName, $alias);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getChildNames($parentName)
    {
        return $this->_getSubject()->getChildNames($parentName);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getChildBlocks($parentName)
    {
        return $this->_getSubject()->getChildBlocks($parentName);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getChildName($parentName, $alias)
    {
        return $this->_getSubject()->getChildName($parentName, $alias);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function renderElement($name, $useCache = true)
    {
        return $this->_getSubject()->renderElement($name, $useCache);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function renderNonCachedElement($name)
    {
        return $this->_getSubject()->renderNonCachedElement($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addToParentGroup($blockName, $parentGroupName)
    {
        return $this->_getSubject()->addToParentGroup($blockName, $parentGroupName);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getGroupChildNames($blockName, $groupName)
    {
        return $this->_getSubject()->getGroupChildNames($blockName, $groupName);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function hasElement($name)
    {
        return $this->_getSubject()->hasElement($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getElementProperty($name, $attribute)
    {
        return $this->_getSubject()->getElementProperty($name, $attribute);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isBlock($name)
    {
        return $this->_getSubject()->isBlock($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isUiComponent($name)
    {
        return $this->_getSubject()->isUiComponent($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isContainer($name)
    {
        return $this->_getSubject()->isContainer($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isManipulationAllowed($name)
    {
        return $this->_getSubject()->isManipulationAllowed($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBlock($name, $block)
    {
        return $this->_getSubject()->setBlock($name, $block);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function unsetElement($name)
    {
        return $this->_getSubject()->unsetElement($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function createBlock($type, $name = '', array $arguments = [])
    {
        return $this->_getSubject()->createBlock($type, $name, $arguments);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '')
    {
        return $this->_getSubject()->addBlock($block, $name, $parent, $alias);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addContainer($name, $label, array $options = [], $parent = '', $alias = '')
    {
        $this->_getSubject()->addContainer($name, $label, $options, $parent, $alias);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function renameElement($oldName, $newName)
    {
        return $this->_getSubject()->renameElement($oldName, $newName);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAllBlocks()
    {
        return $this->_getSubject()->getAllBlocks();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBlock($name)
    {
        return $this->_getSubject()->getBlock($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUiComponent($name)
    {
        return $this->_getSubject()->getUiComponent($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getParentName($childName)
    {
        return $this->_getSubject()->getParentName($childName);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getElementAlias($name)
    {
        return $this->_getSubject()->getElementAlias($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addOutputElement($name)
    {
        return $this->_getSubject()->addOutputElement($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function removeOutputElement($name)
    {
        return $this->_getSubject()->removeOutputElement($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getOutput()
    {
        return $this->_getSubject()->getOutput();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getMessagesBlock()
    {
        return $this->_getSubject()->getMessagesBlock();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBlockSingleton($type)
    {
        return $this->_getSubject()->getBlockSingleton($type);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRendererOptions($namespace, $staticType, $dynamicType)
    {
        return $this->_getSubject()->getRendererOptions($namespace, $staticType, $dynamicType);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function executeRenderer($namespace, $staticType, $dynamicType, $data = [])
    {
        $this->_getSubject()->executeRenderer($namespace, $staticType, $dynamicType, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function initMessages($messageGroups = [])
    {
        $this->_getSubject()->initMessages($messageGroups);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isCacheable()
    {
        return $this->_getSubject()->isCacheable();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isPrivate()
    {
        return $this->_getSubject()->isPrivate();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIsPrivate($isPrivate = true)
    {
        return $this->_getSubject()->setIsPrivate($isPrivate);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getReaderContext()
    {
        return $this->_getSubject()->getReaderContext();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setXml(\Magento\Framework\Simplexml\Element $node)
    {
        return $this->_getSubject()->setXml($node);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getNode($path = null)
    {
        return $this->_getSubject()->getNode($path);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getXpath($xpath)
    {
        return $this->_getSubject()->getXpath($xpath);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getXmlString()
    {
        return $this->_getSubject()->getXmlString();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function loadFile($filePath)
    {
        return $this->_getSubject()->loadFile($filePath);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function loadString($string)
    {
        return $this->_getSubject()->loadString($string);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function loadDom(\DOMNode $dom)
    {
        return $this->_getSubject()->loadDom($dom);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setNode($path, $value, $overwrite = true)
    {
        return $this->_getSubject()->setNode($path, $value, $overwrite);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function applyExtends()
    {
        return $this->_getSubject()->applyExtends();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function processFileData($text)
    {
        return $this->_getSubject()->processFileData($text);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function extend(\Magento\Framework\Simplexml\Config $config, $overwrite = true)
    {
        return $this->_getSubject()->extend($config, $overwrite);
    }
}
