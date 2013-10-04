<?php

/**
 * Web API Config Model.
 *
 * This is a parent class for storing information about Web API. Most of it is needed by REST.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model;

class Config
{
    const CACHE_ID = 'webapi';

    /**#@+
     * Attributes and nodes used in webapi.xml config.
     */
    const ATTR_SERVICE_CLASS = 'class';
    const ATTR_SERVICE_PATH = 'baseUrl';
    const ATTR_SERVICE_METHOD = 'method';
    const ATTR_HTTP_METHOD = 'httpMethod';
    const ATTR_IS_SECURE = 'isSecure';
    const REST_ROUTE = 'rest-route';
    /**#@-*/

    /**
     * Pattern for Web API interface name.
     */
    const SERVICE_CLASS_PATTERN = '/^(.+?)\\\\(.+?)\\\\Service(\\\\.+)+(V\d+)Interface$/';

    /**
     * @var \Magento\Core\Model\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Webapi\Model\Config\Reader
     */
    protected $_reader;

    /**
     * Module configuration reader
     *
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_moduleReader;

    /** @var \Magento\ObjectManager */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_services;

    /**
     * @param \Magento\Webapi\Model\Cache\Type $configCacheType
     * @param \Magento\Core\Model\Config\Modules\Reader $moduleReader
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Webapi\Model\Cache\Type $configCacheType,
        \Magento\Core\Model\Config\Modules\Reader $moduleReader,
        \Magento\ObjectManager $objectManager
    ) {
        $this->_configCacheType = $configCacheType;
        $this->_moduleReader = $moduleReader;
        $this->_objectManager = $objectManager;
    }

    /**
     * Retrieve list of service files from each module
     *
     * @return array
     */
    protected function _getConfigFile()
    {
        $files = $this->_moduleReader->getConfigurationFiles('webapi.xml');
        return (array)$files;
    }

    /**
     * Reader object initialization
     *
     * @return \Magento\Webapi\Model\Config\Reader
     */
    protected function _getReader()
    {
        if (null === $this->_reader) {
            $configFiles = $this->_getConfigFile();
            $this->_reader = $this->_objectManager->create(
                'Magento\Webapi\Model\Config\Reader',
                array('configFiles' => $configFiles)
            );
        }
        return $this->_reader;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->_services) {
            $services = $this->_loadFromCache();
            if ($services && is_string($services)) {
                $data = unserialize($services);
            } else {
                $services = $this->_getReader()->getServices();
                $data = $this->_toArray($services);
                $this->_saveToCache(serialize($data));
            }
            $this->_services = isset($data['config']) ? $data['config'] : array();
        }
        return $this->_services;
    }

    /**
     * Load services from cache
     */
    protected function _loadFromCache()
    {
        return $this->_configCacheType->load(self::CACHE_ID);
    }

    /**
     * Save services into the cache
     *
     * @param string $data serialized version of the webapi registry
     * @return \Magento\Webapi\Model\Config
     */
    protected function _saveToCache($data)
    {
        $this->_configCacheType->save($data, self::CACHE_ID, array(\Magento\Webapi\Model\Cache\Type::CACHE_TAG));
        return $this;
    }

    /**
     * Get node ID of \DOMNode class
     *
     * @param array $children - Child nodes of a \DOMNode
     * @param \DOMNode $child
     * @return string
     */
    protected function _getNodeId($children, $child)
    {
        $nodeId = isset($children[self::ATTR_SERVICE_CLASS]) ? $children[self::ATTR_SERVICE_CLASS] :
            (isset($children[self::ATTR_SERVICE_METHOD]) ? $children[self::ATTR_SERVICE_METHOD] : $child->nodeName);

        return $nodeId;
    }

    /**
     * Convert attributes of a \DOMNode into an associative array.
     *
     * @param \DOMNode $node
     * @return array
     */
    protected function _getAttributes($node)
    {
        $attributes = array();

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }
        }

        return $attributes;
    }

    /**
     * Check the first \DOMNode in a \DOMNodeList of size 1 to see if it's an XML_TEXT_NODE and store it's value.
     *
     * @param array $result - Associative array of attributes from a \DOMNode
     * @param \DOMNodeList $children - Child nodes of a \DOMNode
     * @return array|string|null
     */
    protected function _checkForTextNode($result, $children)
    {
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['value'] = $child->nodeValue;
                return count($result) == 1 ? $result['value'] : $result;
            }
        }

        return null;
    }

    /**
     * Process all child nodes of a root \DOMNode, establishing all operations, routes, etc.
     *
     * @param array $result
     * @param \DOMNodeList $children
     * @return array
     */
    protected function _processChildren($result, $children)
    {
        $group = array();

        for ($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            $childAsArray = $this->_toArray($child);

            $nodeId = $this->_getNodeId($childAsArray, $child);

            if (self::REST_ROUTE === $child->nodeName) {
                if (!isset($result['methods'])) {
                    $result['methods'] = array();
                }

                $result['methods'][$nodeId] = isset($result['methods'][$nodeId])
                    ? array_merge($result['methods'][$nodeId], $childAsArray)
                    : $childAsArray;

                if (isset($result['methods'][$nodeId]['value'])) {
                    $result['methods'][$nodeId]['route'] = $result['methods'][$nodeId]['value'];
                    unset($result['methods'][$nodeId]['value']);
                }
            } else {
                if (!isset($result[$nodeId])) {
                    $result[$nodeId] = $childAsArray;
                } else {
                    if (!isset($group[$nodeId])) {
                        $tmp = $result[$nodeId];
                        $result[$nodeId] = array($tmp);
                        $group[$nodeId] = 1;
                    }
                    $result[$nodeId][] = $childAsArray;
                }
            }
        }

        return $result;
    }
    /**
     * Convert elements to array
     *
     * @param \DOMNode $root
     * @return array|string
     */
    protected function _toArray($root)
    {
        $result = $this->_getAttributes($root);

        $children = $root->childNodes;
        if ($children) {
            $checkResult = $this->_checkForTextNode($result, $children);
            if ($checkResult != null) {
                return $checkResult;
            }

            $result = $this->_processChildren($result, $children);
        }
        unset($result['#text']);

        return $result;
    }
}
