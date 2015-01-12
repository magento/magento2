<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Proxy class for \Magento\Framework\View\Layout
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Proxy extends \Magento\Framework\View\Layout
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\View\Layout
     */
    protected $subject;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $isShared;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'Magento\Framework\View\Layout',
        $shared = true
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->isShared = $shared;
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
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     */
    public function __clone()
    {
        $this->subject = clone $this->getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\View\Layout
     */
    protected function getSubject()
    {
        if (!$this->subject) {
            $this->subject = true === $this->isShared
                ? $this->objectManager->get($this->instanceName)
                : $this->objectManager->create($this->instanceName);
        }
        return $this->subject;
    }

    /**
     * Retrieve the layout update instance
     *
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    public function getUpdate()
    {
        return $this->getSubject()->getUpdate();
    }

    /**
     * Layout xml generation
     *
     * @return $this
     */
    public function generateXml()
    {
        return $this->getSubject()->generateXml();
    }

    /**
     * Create structure of elements from the loaded XML configuration
     *
     * @return void
     */
    public function generateElements()
    {
        $this->getSubject()->generateElements();
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
        return $this->getSubject()->getChildBlock($parentName, $alias);
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
        return $this->getSubject()->setChild($parentName, $elementName, $alias);
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
        $this->getSubject()->reorderChild($parentName, $childName, $offsetOrSibling, $after);
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
        return $this->getSubject()->unsetChild($parentName, $alias);
    }

    /**
     * Get list of child names
     *
     * @param string $parentName
     * @return array
     */
    public function getChildNames($parentName)
    {
        return $this->getSubject()->getChildNames($parentName);
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
        return $this->getSubject()->getChildBlocks($parentName);
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
        return $this->getSubject()->getChildName($parentName, $alias);
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
        return $this->getSubject()->renderElement($name, $useCache);
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
        return $this->getSubject()->addToParentGroup($blockName, $parentGroupName);
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
        return $this->getSubject()->getGroupChildNames($blockName, $groupName);
    }

    /**
     * Check if element exists in layout structure
     *
     * @param string $name
     * @return bool
     */
    public function hasElement($name)
    {
        return $this->getSubject()->hasElement($name);
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
        return $this->getSubject()->getElementProperty($name, $attribute);
    }

    /**
     * Whether specified element is a block
     *
     * @param string $name
     * @return bool
     */
    public function isBlock($name)
    {
        return $this->getSubject()->isBlock($name);
    }

    /**
     * Checks if element with specified name is container
     *
     * @param string $name
     * @return bool
     */
    public function isContainer($name)
    {
        return $this->getSubject()->isContainer($name);
    }

    /**
     * Whether the specified element may be manipulated externally
     *
     * @param string $name
     * @return bool
     */
    public function isManipulationAllowed($name)
    {
        return $this->getSubject()->isManipulationAllowed($name);
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
        return $this->getSubject()->setBlock($name, $block);
    }

    /**
     * Remove block from registry
     *
     * @param string $name
     * @return $this
     */
    public function unsetElement($name)
    {
        return $this->getSubject()->unsetElement($name);
    }

    /**
     * Block Factory
     *
     * @param  string $type
     * @param  string $name
     * @param  array $attributes
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function createBlock($type, $name = '', array $attributes = [])
    {
        return $this->getSubject()->createBlock($type, $name, $attributes);
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
        return $this->getSubject()->addBlock($block, $name, $parent, $alias);
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
    public function addContainer($name, $label, array $options = [], $parent = '', $alias = '')
    {
        $this->getSubject()->addContainer($name, $label, $options, $parent, $alias);
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
        return $this->getSubject()->renameElement($oldName, $newName);
    }

    /**
     * Retrieve all blocks from registry as array
     *
     * @return array
     */
    public function getAllBlocks()
    {
        return $this->getSubject()->getAllBlocks();
    }

    /**
     * Get block object by name
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function getBlock($name)
    {
        return $this->getSubject()->getBlock($name);
    }

    /**
     * Gets parent name of an element with specified name
     *
     * @param string $childName
     * @return bool|string
     */
    public function getParentName($childName)
    {
        return $this->getSubject()->getParentName($childName);
    }

    /**
     * Get element alias by name
     *
     * @param string $name
     * @return bool|string
     */
    public function getElementAlias($name)
    {
        return $this->getSubject()->getElementAlias($name);
    }

    /**
     * Add an element to output
     *
     * @param string $name
     * @return $this
     */
    public function addOutputElement($name)
    {
        return $this->getSubject()->addOutputElement($name);
    }

    /**
     * Remove an element from output
     *
     * @param string $name
     * @return $this
     */
    public function removeOutputElement($name)
    {
        return $this->getSubject()->removeOutputElement($name);
    }

    /**
     * Get all blocks marked for output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->getSubject()->getOutput();
    }

    /**
     * Retrieve messages block
     *
     * @return \Magento\Framework\View\Element\Messages
     */
    public function getMessagesBlock()
    {
        return $this->getSubject()->getMessagesBlock();
    }

    /**
     * Get block singleton
     *
     * @param string $type
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\App\Helper\AbstractHelper
     */
    public function getBlockSingleton($type)
    {
        return $this->getSubject()->getBlockSingleton($type);
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
    public function addAdjustableRenderer($namespace, $staticType, $dynamicType, $type, $template, $data = [])
    {
        return $this->getSubject()->addAdjustableRenderer(
            $namespace,
            $staticType,
            $dynamicType,
            $type,
            $template,
            $data
        );
    }

    /**
     * Get renderer options
     *
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @return array|null
     */
    public function getRendererOptions($namespace, $staticType, $dynamicType)
    {
        return $this->getSubject()->getRendererOptions($namespace, $staticType, $dynamicType);
    }

    /**
     * Execute renderer
     *
     * @param string $namespace
     * @param string $staticType
     * @param string $dynamicType
     * @param array $data
     * @return void
     */
    public function executeRenderer($namespace, $staticType, $dynamicType, $data = [])
    {
        $this->getSubject()->executeRenderer($namespace, $staticType, $dynamicType, $data);
    }

    /**
     * Init messages by message storage(s), loading and adding messages to layout messages block
     *
     * @param string|array $messageGroups
     * @return void
     */
    public function initMessages($messageGroups = [])
    {
        $this->getSubject()->initMessages($messageGroups);
    }

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isCacheable()
    {
        return $this->getSubject()->isCacheable();
    }

    /**
     * Check is exists non-cacheable layout elements
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->getSubject()->isPrivate();
    }

    /**
     * Mark layout as private
     *
     * @param bool $isPrivate
     * @return $this
     */
    public function setIsPrivate($isPrivate = true)
    {
        return $this->getSubject()->setIsPrivate($isPrivate);
    }

    /**
     * Sets xml for this configuration
     *
     * @param \Magento\Framework\Simplexml\Element $node
     * @return $this
     */
    public function setXml(\Magento\Framework\Simplexml\Element $node)
    {
        return $this->getSubject()->setXml($node);
    }

    /**
     * Returns node found by the $path
     *
     * @param string $path
     * @return \Magento\Framework\Simplexml\Element|bool
     * @see \Magento\Framework\Simplexml\Element::descend
     */
    public function getNode($path = null)
    {
        return $this->getSubject()->getNode($path);
    }

    /**
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return \SimpleXMLElement[]|bool
     */
    public function getXpath($xpath)
    {
        return $this->getSubject()->getXpath($xpath);
    }

    /**
     * Set cache
     *
     * @param \Magento\Framework\Simplexml\Config\Cache\AbstractCache $cache
     * @return $this
     */
    public function setCache($cache)
    {
        return $this->getSubject()->setCache($cache);
    }

    /**
     * Return cache
     *
     * @return \Magento\Framework\Simplexml\Config\Cache\AbstractCache
     */
    public function getCache()
    {
        return $this->getSubject()->getCache();
    }

    /**
     * Set whether cache is saved
     *
     * @param boolean $flag
     * @return $this
     */
    public function setCacheSaved($flag)
    {
        return $this->getSubject()->setCacheSaved($flag);
    }

    /**
     * Return whether cache is saved
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCacheSaved()
    {
        return $this->getSubject()->getCacheSaved();
    }

    /**
     * Set cache ID
     *
     * @param string $id
     * @return $this
     */
    public function setCacheId($id)
    {
        return $this->getSubject()->setCacheId($id);
    }

    /**
     * Return cache ID
     *
     * @return string
     */
    public function getCacheId()
    {
        return $this->getSubject()->getCacheId();
    }

    /**
     * Set cache tags
     *
     * @param array $tags
     * @return $this
     */
    public function setCacheTags($tags)
    {
        return $this->getSubject()->setCacheTags($tags);
    }

    /**
     * Return cache tags
     *
     * @return array
     */
    public function getCacheTags()
    {
        return $this->getSubject()->getCacheTags();
    }

    /**
     * Set cache lifetime
     *
     * @param int $lifetime
     * @return $this
     */
    public function setCacheLifetime($lifetime)
    {
        return $this->getSubject()->setCacheLifetime($lifetime);
    }

    /**
     * Return cache lifetime
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return $this->getSubject()->getCacheLifetime();
    }

    /**
     * Set cache checksum
     *
     * @param string $data
     * @return $this
     */
    public function setCacheChecksum($data)
    {
        return $this->getSubject()->setCacheChecksum($data);
    }

    /**
     * Update cache checksum
     *
     * @param string $data
     * @return $this
     */
    public function updateCacheChecksum($data)
    {
        return $this->getSubject()->updateCacheChecksum($data);
    }

    /**
     * Return cache checksum
     *
     * @return string
     */
    public function getCacheChecksum()
    {
        return $this->getSubject()->getCacheChecksum();
    }

    /**
     * Get cache checksum ID
     *
     * @return string
     */
    public function getCacheChecksumId()
    {
        return $this->getSubject()->getCacheChecksumId();
    }

    /**
     * Fetch cache checksum
     *
     * @return boolean
     */
    public function fetchCacheChecksum()
    {
        return $this->getSubject()->fetchCacheChecksum();
    }

    /**
     * Validate cache checksum
     *
     * @return boolean
     */
    public function validateCacheChecksum()
    {
        return $this->getSubject()->validateCacheChecksum();
    }

    /**
     * Load cache
     *
     * @return boolean
     */
    public function loadCache()
    {
        return $this->getSubject()->loadCache();
    }

    /**
     * Save cache
     *
     * @param array $tags
     * @return $this
     */
    public function saveCache($tags = null)
    {
        return $this->getSubject()->saveCache($tags);
    }

    /**
     * Return Xml of node as string
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->getSubject()->getXmlString();
    }

    /**
     * Remove cache
     *
     * @return $this
     */
    public function removeCache()
    {
        return $this->getSubject()->removeCache();
    }

    /**
     * Imports XML file
     *
     * @param string $filePath
     * @return boolean
     */
    public function loadFile($filePath)
    {
        return $this->getSubject()->loadFile($filePath);
    }

    /**
     * Imports XML string
     *
     * @param string $string
     * @return boolean
     */
    public function loadString($string)
    {
        return $this->getSubject()->loadString($string);
    }

    /**
     * Imports DOM node
     *
     * @param \DOMNode $dom
     * @return bool
     */
    public function loadDom($dom)
    {
        return $this->getSubject()->loadDom($dom);
    }

    /**
     * Create node by $path and set its value.
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param boolean $overwrite
     * @return $this
     */
    public function setNode($path, $value, $overwrite = true)
    {
        return $this->getSubject()->setNode($path, $value, $overwrite);
    }

    /**
     * Process configuration xml
     *
     * @return $this
     */
    public function applyExtends()
    {
        return $this->getSubject()->applyExtends();
    }

    /**
     * Stub method for processing file data right after loading the file text
     *
     * @param string $text
     * @return string
     */
    public function processFileData($text)
    {
        return $this->getSubject()->processFileData($text);
    }

    /**
     * Extend configuration
     *
     * @param \Magento\Framework\Simplexml\Config $config
     * @param boolean $overwrite
     * @return $this
     */
    public function extend(\Magento\Framework\Simplexml\Config $config, $overwrite = true)
    {
        return $this->getSubject()->extend($config, $overwrite);
    }
}
