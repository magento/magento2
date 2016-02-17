<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tag
 * @subpackage Cloud
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tag_Item
 */
#require_once 'Zend/Tag/Item.php';

/**
 * @category   Zend
 * @package    Zend_Tag
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tag_Cloud
{
    /**
     * Decorator for the cloud
     *
     * @var Zend_Tag_Cloud_Decorator_Cloud
     */
    protected $_cloudDecorator = null;

    /**
     * Decorator for the tags
     *
     * @var Zend_Tag_Cloud_Decorator_Tag
     */
    protected $_tagDecorator = null;

    /**
     * List of all tags
     *
     * @var Zend_Tag_ItemList
     */
    protected $_tags = null;

    /**
     * Plugin loader for decorators
     *
     * @var Zend_Loader_PluginLoader
     */
    protected $_pluginLoader = null;

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $_skipOptions = array(
        'options',
        'config',
    );

    /**
     * Create a new tag cloud with options
     *
     * @param mixed $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set options from Zend_Config
     *
     * @param  Zend_Config $config
     * @return Zend_Tag_Cloud
     */
    public function setConfig(Zend_Config $config)
    {
        $this->setOptions($config->toArray());

        return $this;
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for Zend_Tag_Cloud
     * @return Zend_Tag_Cloud
     */
    public function setOptions(array $options)
    {
        if (isset($options['prefixPath'])) {
            $this->addPrefixPaths($options['prefixPath']);
            unset($options['prefixPath']);
        }

        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->_skipOptions)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Set the tags for the tag cloud.
     *
     * $tags should be an array containing single tags as array. Each tag
     * array should at least contain the keys 'title' and 'weight'. Optionally
     * you may supply the key 'url', to which the tag links to. Any additional
     * parameter in the array is silently ignored and can be used by custom
     * decorators.
     *
     * @param  array $tags
     * @return Zend_Tag_Cloud
     */
    public function setTags(array $tags)
    {
        // Validate and cleanup the tags
        $itemList = $this->getItemList();

        foreach ($tags as $tag) {
            if ($tag instanceof Zend_Tag_Taggable) {
                $itemList[] = $tag;
            } else if (is_array($tag)) {
                $itemList[] = new Zend_Tag_Item($tag);
            } else {
                #require_once 'Zend/Tag/Cloud/Exception.php';
                throw new Zend_Tag_Cloud_Exception('Tag must be an instance of Zend_Tag_Taggable or an array');
            }
        }

        return $this;
    }

    /**
     * Append a single tag to the cloud
     *
     * @param  Zend_Tag_Taggable|array $tag
     * @return Zend_Tag_Cloud
     */
    public function appendTag($tag)
    {
        $tags = $this->getItemList();
        if ($tag instanceof Zend_Tag_Taggable) {
            $tags[] = $tag;
        } else if (is_array($tag)) {
            $tags[] = new Zend_Tag_Item($tag);
        } else {
            #require_once 'Zend/Tag/Cloud/Exception.php';
            throw new Zend_Tag_Cloud_Exception('Tag must be an instance of Zend_Tag_Taggable or an array');
        }

        return $this;
    }

    /**
     * Set the item list
     *
     * @param  Zend_Tag_ItemList $itemList
     * @return Zend_Tag_Cloud
     */
    public function setItemList(Zend_Tag_ItemList $itemList)
    {
        $this->_tags = $itemList;
        return $this;
    }

    /**
     * Retrieve the item list
     *
     * If item list is undefined, creates one.
     *
     * @return Zend_Tag_ItemList
     */
    public function getItemList()
    {
        if (null === $this->_tags) {
            #require_once 'Zend/Tag/ItemList.php';
            $this->setItemList(new Zend_Tag_ItemList());
        }
        return $this->_tags;
    }

    /**
     * Set the decorator for the cloud
     *
     * @param  mixed $decorator
     * @return Zend_Tag_Cloud
     */
    public function setCloudDecorator($decorator)
    {
        $options = null;

        if (is_array($decorator)) {
            if (isset($decorator['options'])) {
                $options = $decorator['options'];
            }

            if (isset($decorator['decorator'])) {
                $decorator = $decorator['decorator'];
            }
        }

        if (is_string($decorator)) {
            $classname = $this->getPluginLoader()->load($decorator);
            $decorator = new $classname($options);
        }

        if (!($decorator instanceof Zend_Tag_Cloud_Decorator_Cloud)) {
            #require_once 'Zend/Tag/Cloud/Exception.php';
            throw new Zend_Tag_Cloud_Exception('Decorator is no instance of Zend_Tag_Cloud_Decorator_Cloud');
        }

        $this->_cloudDecorator = $decorator;

        return $this;
    }

    /**
     * Get the decorator for the cloud
     *
     * @return Zend_Tag_Cloud_Decorator_Cloud
     */
    public function getCloudDecorator()
    {
        if (null === $this->_cloudDecorator) {
            $this->setCloudDecorator('htmlCloud');
        }
        return $this->_cloudDecorator;
    }

    /**
     * Set the decorator for the tags
     *
     * @param  mixed $decorator
     * @return Zend_Tag_Cloud
     */
    public function setTagDecorator($decorator)
    {
        $options = null;

        if (is_array($decorator)) {
            if (isset($decorator['options'])) {
                $options = $decorator['options'];
            }

            if (isset($decorator['decorator'])) {
                $decorator = $decorator['decorator'];
            }
        }

        if (is_string($decorator)) {
            $classname = $this->getPluginLoader()->load($decorator);
            $decorator = new $classname($options);
        }

        if (!($decorator instanceof Zend_Tag_Cloud_Decorator_Tag)) {
            #require_once 'Zend/Tag/Cloud/Exception.php';
            throw new Zend_Tag_Cloud_Exception('Decorator is no instance of Zend_Tag_Cloud_Decorator_Tag');
        }

        $this->_tagDecorator = $decorator;

        return $this;
    }

    /**
     * Get the decorator for the tags
     *
     * @return Zend_Tag_Cloud_Decorator_Tag
     */
    public function getTagDecorator()
    {
        if (null === $this->_tagDecorator) {
            $this->setTagDecorator('htmlTag');
        }
        return $this->_tagDecorator;
    }

    /**
     * Set plugin loaders for use with decorators
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return Zend_Tag_Cloud
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    /**
     * Get the plugin loader for decorators
     *
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $prefix     = 'Zend_Tag_Cloud_Decorator_';
            $pathPrefix = 'Zend/Tag/Cloud/Decorator/';

            #require_once 'Zend/Loader/PluginLoader.php';
            $this->_pluginLoader = new Zend_Loader_PluginLoader(array($prefix => $pathPrefix));
        }

        return $this->_pluginLoader;
    }

    /**
     * Add many prefix paths at once
     *
     * @param  array $paths
     * @return Zend_Tag_Cloud
     */
    public function addPrefixPaths(array $paths)
    {
        if (isset($paths['prefix']) && isset($paths['path'])) {
            return $this->addPrefixPath($paths['prefix'], $paths['path']);
        }

        foreach ($paths as $path) {
            if (!isset($path['prefix']) || !isset($path['path'])) {
                continue;
            }

            $this->addPrefixPath($path['prefix'], $path['path']);
        }

        return $this;
    }

    /**
     * Add prefix path for plugin loader
     *
     * @param  string $prefix
     * @param  string $path
     * @return Zend_Tag_Cloud
     */
    public function addPrefixPath($prefix, $path)
    {
        $loader = $this->getPluginLoader();
        $loader->addPrefixPath($prefix, $path);

        return $this;
    }

    /**
     * Render the tag cloud
     *
     * @return string
     */
    public function render()
    {
        $tags = $this->getItemList();

        if (count($tags) === 0) {
            return '';
        }

        $tagsResult  = $this->getTagDecorator()->render($tags);
        $cloudResult = $this->getCloudDecorator()->render($tagsResult);

        return $cloudResult;
    }

    /**
     * Render the tag cloud
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $result = $this->render();
            return $result;
        } catch (Exception $e) {
            $message = "Exception caught by tag cloud: " . $e->getMessage()
                     . "\nStack Trace:\n" . $e->getTraceAsString();
            trigger_error($message, E_USER_WARNING);
            return '';
        }
    }
}
