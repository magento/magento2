<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Simplexml;

/**
 * Base class for simplexml based configurations
 */
class Config
{
    /**
     * Configuration xml
     *
     * @var Element
     */
    protected $_xml = null;

    /**
     * Enter description here...
     *
     * @var string
     */
    protected $_cacheId = null;

    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_cacheTags = [];

    /**
     * Enter description here...
     *
     * @var int
     */
    protected $_cacheLifetime = null;

    /**
     * Enter description here...
     *
     * @var string|null|false
     */
    protected $_cacheChecksum = false;

    /**
     * Enter description here...
     *
     * @var boolean
     */
    protected $_cacheSaved = false;

    /**
     * Cache resource object
     *
     * @var Config\Cache\AbstractCache
     */
    protected $_cache = null;

    /**
     * Class name of simplexml elements for this configuration
     *
     * @var string
     */
    protected $_elementClass = \Magento\Framework\Simplexml\Element::class;

    /**
     * Xpath describing nodes in configuration that need to be extended
     *
     * @example <allResources extends="/config/modules//resource"/>
     */
    protected $_xpathExtends = "//*[@extends]";

    /**
     * Constructor
     *
     * Initializes XML for this configuration
     *
     * @see \Magento\Framework\Simplexml\Config::setXml
     * @param string|Element $sourceData
     */
    public function __construct($sourceData = null)
    {
        if ($sourceData === null) {
            return;
        }
        if ($sourceData instanceof Element) {
            $this->setXml($sourceData);
        } elseif (is_string($sourceData) && !empty($sourceData)) {
            if (strlen($sourceData) < 1000 && is_readable($sourceData)) {
                $this->loadFile($sourceData);
            } else {
                $this->loadString($sourceData);
            }
        }
    }

    /**
     * Sets xml for this configuration
     *
     * @param Element $node
     * @return $this
     */
    public function setXml(Element $node)
    {
        $this->_xml = $node;
        return $this;
    }

    /**
     * Returns node found by the $path
     *
     * @see \Magento\Framework\Simplexml\Element::descend
     * @param string $path
     * @return Element|bool
     */
    public function getNode($path = null)
    {
        if (!$this->getXml() instanceof Element) {
            return false;
        } elseif ($path === null) {
            return $this->getXml();
        } else {
            return $this->getXml()->descend($path);
        }
    }

    /**
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return Element[]|bool
     */
    public function getXpath($xpath)
    {
        $xml = $this->getXml();
        if (empty($xml)) {
            return false;
        }

        if (!($result = @$xml->xpath($xpath))) {
            return false;
        }

        return $result;
    }

    /**
     * Enter description here...
     *
     * @param Config\Cache\AbstractCache $cache
     * @return $this
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Config\Cache\AbstractCache
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Enter description here...
     *
     * @param boolean $flag
     * @return $this
     */
    public function setCacheSaved($flag)
    {
        $this->_cacheSaved = $flag;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getCacheSaved()
    {
        return $this->_cacheSaved;
    }

    /**
     * Enter description here...
     *
     * @param string $id
     * @return $this
     */
    public function setCacheId($id)
    {
        $this->_cacheId = $id;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCacheId()
    {
        return $this->_cacheId;
    }

    /**
     * Enter description here...
     *
     * @param array $tags
     * @return $this
     */
    public function setCacheTags($tags)
    {
        $this->_cacheTags = $tags;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getCacheTags()
    {
        return $this->_cacheTags;
    }

    /**
     * Enter description here...
     *
     * @param int $lifetime
     * @return $this
     */
    public function setCacheLifetime($lifetime)
    {
        $this->_cacheLifetime = $lifetime;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return $this->_cacheLifetime;
    }

    /**
     * Enter description here...
     *
     * @param string $data
     * @return $this
     */
    public function setCacheChecksum($data)
    {
        if ($data === null) {
            $this->_cacheChecksum = null;
        } elseif (false === $data || 0 === $data) {
            $this->_cacheChecksum = false;
        } else {
            $this->_cacheChecksum = md5($data);
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string $data
     * @return $this
     */
    public function updateCacheChecksum($data)
    {
        if (false === $this->getCacheChecksum()) {
            return $this;
        }
        if (false === $data || 0 === $data) {
            $this->_cacheChecksum = false;
        } else {
            $this->setCacheChecksum($this->getCacheChecksum() . ':' . $data);
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCacheChecksum()
    {
        return $this->_cacheChecksum;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCacheChecksumId()
    {
        return $this->getCacheId() . '__CHECKSUM';
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function fetchCacheChecksum()
    {
        return false;
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function validateCacheChecksum()
    {
        $newChecksum = $this->getCacheChecksum();
        if (false === $newChecksum) {
            return false;
        }
        if ($newChecksum === null) {
            return true;
        }
        $cachedChecksum = $this->getCache()->load($this->getCacheChecksumId());
        return $newChecksum === false && $cachedChecksum === false || $newChecksum === $cachedChecksum;
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function loadCache()
    {
        if (!$this->validateCacheChecksum()) {
            return false;
        }

        $xmlString = $this->_loadCache($this->getCacheId());
        if ($this->loadString($xmlString)) {
            $this->setCacheSaved(true);
            return true;
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @param array $tags
     * @return $this
     */
    public function saveCache($tags = null)
    {
        if ($this->getCacheSaved() || $this->getCacheChecksum() === false) {
            return $this;
        }

        if ($tags === null) {
            $tags = $this->getCacheTags();
        }

        if ($this->getCacheChecksum() === null) {
            $this->_saveCache($this->getCacheChecksum(), $this->getCacheChecksumId(), $tags, $this->getCacheLifetime());
        }

        $this->_saveCache($this->getXmlString(), $this->getCacheId(), $tags, $this->getCacheLifetime());
        $this->setCacheSaved(true);

        return $this;
    }

    /**
     * Return Xml of node as string
     *
     * @return string
     */
    public function getXmlString()
    {
        return $this->getNode()->asNiceXml('', false);
    }

    /**
     * Enter description here...
     *
     * @return $this
     */
    public function removeCache()
    {
        $this->_removeCache($this->getCacheId());
        $this->_removeCache($this->getCacheChecksumId());
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string $id
     * @return boolean
     */
    protected function _loadCache($id)
    {
        return $this->getCache()->load($id);
    }

    /**
     * Enter description here...
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @param int|boolean $lifetime
     * @return boolean
     */
    protected function _saveCache($data, $id, $tags = [], $lifetime = false)
    {
        return $this->getCache()->save($data, $id, $tags, $lifetime);
    }

    /**
     * Enter description here...
     *
     * @param string $id
     * @return mixed
     * @todo check this, as there are no caches that implement remove() method
     */
    protected function _removeCache($id)
    {
        return $this->getCache()->remove($id);
    }

    /**
     * Imports XML file
     *
     * @param string $filePath
     * @return boolean
     */
    public function loadFile($filePath)
    {
        if (!is_readable($filePath)) {
            //throw new \Exception('Can not read xml file '.$filePath);
            return false;
        }

        $fileData = file_get_contents($filePath);
        $fileData = $this->processFileData($fileData);
        return $this->loadString($fileData);
    }

    /**
     * Imports XML string
     *
     * @param string $string
     * @return boolean
     */
    public function loadString($string)
    {
        if (!empty($string)) {
            $xml = simplexml_load_string($string, $this->_elementClass);
            if ($xml) {
                $this->setXml($xml);
                return true;
            }
        }
        return false;
    }

    /**
     * Imports DOM node
     *
     * @param \DOMNode $dom
     * @return bool
     */
    public function loadDom(\DOMNode $dom)
    {
        $xml = simplexml_import_dom($dom, $this->_elementClass);
        if ($xml) {
            $this->setXml($xml);
            return true;
        }

        return false;
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
        $this->getXml()->setNode($path, $value, $overwrite);
        return $this;
    }

    /**
     * Process configuration xml
     *
     * @return $this
     */
    public function applyExtends()
    {
        $targets = $this->getXpath($this->_xpathExtends);
        if (!$targets) {
            return $this;
        }
        foreach ($targets as $target) {
            $sources = $this->getXpath((string)$target['extends']);
            if ($sources) {
                foreach ($sources as $source) {
                    $target->extend($source);
                }
            } else {
                #echo "Not found extend: ".(string)$target['extends'];
            }
            #unset($target['extends']);
        }
        return $this;
    }

    /**
     * Stub method for processing file data right after loading the file text
     *
     * @param string $text
     * @return string
     */
    public function processFileData($text)
    {
        return $text;
    }

    /**
     * Enter description here...
     *
     * @param Config $config
     * @param boolean $overwrite
     * @return $this
     */
    public function extend(Config $config, $overwrite = true)
    {
        $this->getNode()->extend($config->getNode(), $overwrite);
        return $this;
    }

    /**
     * Cleanup circular references
     *
     * Destructor should be called explicitly in order to work around the PHP bug
     * https://bugs.php.net/bug.php?id=62468
     *
     * @return void
     */
    public function __destruct()
    {
        $this->_xml = null;
    }

    /**
     * Getter for xml element
     *
     * @return Element
     */
    protected function getXml()
    {
        return $this->_xml;
    }
}
