<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $instanceName = '\\Magento\\Framework\\View\\Layout',
        $shared = true
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
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
     * {@inheritdoc}
     */
    public function setGeneratorPool(\Magento\Framework\View\Layout\GeneratorPool $generatorPool)
    {
        return $this->_getSubject()->setGeneratorPool($generatorPool);
    }

    /**
     * {@inheritdoc}
     */
    public function setBuilder(\Magento\Framework\View\Layout\BuilderInterface $builder)
    {
        return $this->_getSubject()->setBuilder($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function publicBuild()
    {
        $this->_getSubject()->publicBuild();
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdate()
    {
        return $this->_getSubject()->getUpdate();
    }

    /**
     * {@inheritdoc}
     */
    public function generateXml()
    {
        return $this->_getSubject()->generateXml();
    }

    /**
     * {@inheritdoc}
     */
    public function generateElements()
    {
        $this->_getSubject()->generateElements();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildBlock($parentName, $alias)
    {
        return $this->_getSubject()->getChildBlock($parentName, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function setChild($parentName, $elementName, $alias)
    {
        return $this->_getSubject()->setChild($parentName, $elementName, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function reorderChild($parentName, $childName, $offsetOrSibling, $after = true)
    {
        $this->_getSubject()->reorderChild($parentName, $childName, $offsetOrSibling, $after);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetChild($parentName, $alias)
    {
        return $this->_getSubject()->unsetChild($parentName, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildNames($parentName)
    {
        return $this->_getSubject()->getChildNames($parentName);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildBlocks($parentName)
    {
        return $this->_getSubject()->getChildBlocks($parentName);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildName($parentName, $alias)
    {
        return $this->_getSubject()->getChildName($parentName, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function renderElement($name, $useCache = true)
    {
        return $this->_getSubject()->renderElement($name, $useCache);
    }

    /**
     * {@inheritdoc}
     */
    public function renderNonCachedElement($name)
    {
        return $this->_getSubject()->renderNonCachedElement($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addToParentGroup($blockName, $parentGroupName)
    {
        return $this->_getSubject()->addToParentGroup($blockName, $parentGroupName);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupChildNames($blockName, $groupName)
    {
        return $this->_getSubject()->getGroupChildNames($blockName, $groupName);
    }

    /**
     * {@inheritdoc}
     */
    public function hasElement($name)
    {
        return $this->_getSubject()->hasElement($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getElementProperty($name, $attribute)
    {
        return $this->_getSubject()->getElementProperty($name, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function isBlock($name)
    {
        return $this->_getSubject()->isBlock($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isUiComponent($name)
    {
        return $this->_getSubject()->isUiComponent($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isContainer($name)
    {
        return $this->_getSubject()->isContainer($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isManipulationAllowed($name)
    {
        return $this->_getSubject()->isManipulationAllowed($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setBlock($name, $block)
    {
        return $this->_getSubject()->setBlock($name, $block);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetElement($name)
    {
        return $this->_getSubject()->unsetElement($name);
    }

    /**
     * {@inheritdoc}
     */
    public function createBlock($type, $name = '', array $arguments = [])
    {
        return $this->_getSubject()->createBlock($type, $name, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function addBlock($block, $name = '', $parent = '', $alias = '')
    {
        return $this->_getSubject()->addBlock($block, $name, $parent, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function addContainer($name, $label, array $options = [], $parent = '', $alias = '')
    {
        $this->_getSubject()->addContainer($name, $label, $options, $parent, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function renameElement($oldName, $newName)
    {
        return $this->_getSubject()->renameElement($oldName, $newName);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllBlocks()
    {
        return $this->_getSubject()->getAllBlocks();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlock($name)
    {
        return $this->_getSubject()->getBlock($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getUiComponent($name)
    {
        return $this->_getSubject()->getUiComponent($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentName($childName)
    {
        return $this->_getSubject()->getParentName($childName);
    }

    /**
     * {@inheritdoc}
     */
    public function getElementAlias($name)
    {
        return $this->_getSubject()->getElementAlias($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addOutputElement($name)
    {
        return $this->_getSubject()->addOutputElement($name);
    }

    /**
     * {@inheritdoc}
     */
    public function removeOutputElement($name)
    {
        return $this->_getSubject()->removeOutputElement($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->_getSubject()->getOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessagesBlock()
    {
        return $this->_getSubject()->getMessagesBlock();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockSingleton($type)
    {
        return $this->_getSubject()->getBlockSingleton($type);
    }

    /**
     * {@inheritdoc}
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
     */
    public function getRendererOptions($namespace, $staticType, $dynamicType)
    {
        return $this->_getSubject()->getRendererOptions($namespace, $staticType, $dynamicType);
    }

    /**
     * {@inheritdoc}
     */
    public function executeRenderer($namespace, $staticType, $dynamicType, $data = [])
    {
        $this->_getSubject()->executeRenderer($namespace, $staticType, $dynamicType, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function initMessages($messageGroups = [])
    {
        $this->_getSubject()->initMessages($messageGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheable()
    {
        return $this->_getSubject()->isCacheable();
    }

    /**
     * {@inheritdoc}
     */
    public function isPrivate()
    {
        return $this->_getSubject()->isPrivate();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsPrivate($isPrivate = true)
    {
        return $this->_getSubject()->setIsPrivate($isPrivate);
    }

    /**
     * {@inheritdoc}
     */
    public function getReaderContext()
    {
        return $this->_getSubject()->getReaderContext();
    }

    /**
     * {@inheritdoc}
     */
    public function setXml(\Magento\Framework\Simplexml\Element $node)
    {
        return $this->_getSubject()->setXml($node);
    }

    /**
     * {@inheritdoc}
     */
    public function getNode($path = null)
    {
        return $this->_getSubject()->getNode($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getXpath($xpath)
    {
        return $this->_getSubject()->getXpath($xpath);
    }

    /**
     * {@inheritdoc}
     */
    public function setCache($cache)
    {
        return $this->_getSubject()->setCache($cache);
    }

    /**
     * {@inheritdoc}
     */
    public function getCache()
    {
        return $this->_getSubject()->getCache();
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheSaved($flag)
    {
        return $this->_getSubject()->setCacheSaved($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheSaved()
    {
        return $this->_getSubject()->getCacheSaved();
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheId($id)
    {
        return $this->_getSubject()->setCacheId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        return $this->_getSubject()->getCacheId();
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheTags($tags)
    {
        return $this->_getSubject()->setCacheTags($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheTags()
    {
        return $this->_getSubject()->getCacheTags();
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheLifetime($lifetime)
    {
        return $this->_getSubject()->setCacheLifetime($lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheLifetime()
    {
        return $this->_getSubject()->getCacheLifetime();
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheChecksum($data)
    {
        return $this->_getSubject()->setCacheChecksum($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCacheChecksum($data)
    {
        return $this->_getSubject()->updateCacheChecksum($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheChecksum()
    {
        return $this->_getSubject()->getCacheChecksum();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheChecksumId()
    {
        return $this->_getSubject()->getCacheChecksumId();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCacheChecksum()
    {
        return $this->_getSubject()->fetchCacheChecksum();
    }

    /**
     * {@inheritdoc}
     */
    public function validateCacheChecksum()
    {
        return $this->_getSubject()->validateCacheChecksum();
    }

    /**
     * {@inheritdoc}
     */
    public function loadCache()
    {
        return $this->_getSubject()->loadCache();
    }

    /**
     * {@inheritdoc}
     */
    public function saveCache($tags = null)
    {
        return $this->_getSubject()->saveCache($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getXmlString()
    {
        return $this->_getSubject()->getXmlString();
    }

    /**
     * {@inheritdoc}
     */
    public function removeCache()
    {
        return $this->_getSubject()->removeCache();
    }

    /**
     * {@inheritdoc}
     */
    public function loadFile($filePath)
    {
        return $this->_getSubject()->loadFile($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function loadString($string)
    {
        return $this->_getSubject()->loadString($string);
    }

    /**
     * {@inheritdoc}
     */
    public function loadDom(\DOMNode $dom)
    {
        return $this->_getSubject()->loadDom($dom);
    }

    /**
     * {@inheritdoc}
     */
    public function setNode($path, $value, $overwrite = true)
    {
        return $this->_getSubject()->setNode($path, $value, $overwrite);
    }

    /**
     * {@inheritdoc}
     */
    public function applyExtends()
    {
        return $this->_getSubject()->applyExtends();
    }

    /**
     * {@inheritdoc}
     */
    public function processFileData($text)
    {
        return $this->_getSubject()->processFileData($text);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(\Magento\Framework\Simplexml\Config $config, $overwrite = true)
    {
        return $this->_getSubject()->extend($config, $overwrite);
    }
}
