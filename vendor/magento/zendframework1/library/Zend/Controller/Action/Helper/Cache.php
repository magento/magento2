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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
#require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * @see Zend_Controller_Action_Exception
 */
#require_once 'Zend/Controller/Action/Exception.php';

/**
 * @see Zend_Cache_Manager
 */
#require_once 'Zend/Cache/Manager.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_Cache
    extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Local Cache Manager object used by Helper
     *
     * @var Zend_Cache_Manager
     */
    protected $_manager = null;

    /**
     * Indexed map of Actions to attempt Page caching on by Controller
     *
     * @var array
     */
    protected $_caching = array();

    /**
     * Indexed map of Tags by Controller and Action
     *
     * @var array
     */
    protected $_tags = array();

    /**
     * Indexed map of Extensions by Controller and Action
     *
     * @var array
     */
    protected $_extensions = array();

    /**
     * Track output buffering condition
     */
    protected $_obStarted = false;

    /**
     * Tell the helper which actions are cacheable and under which
     * tags (if applicable) they should be recorded with
     *
     * @param array $actions
     * @param array $tags
     * @return void
     */
    public function direct(array $actions, array $tags = array(), $extension = null)
    {
        $controller = $this->getRequest()->getControllerName();
        $actions = array_unique($actions);
        if (!isset($this->_caching[$controller])) {
            $this->_caching[$controller] = array();
        }
        if (!empty($tags)) {
            $tags = array_unique($tags);
            if (!isset($this->_tags[$controller])) {
                $this->_tags[$controller] = array();
            }
        }
        foreach ($actions as $action) {
            $this->_caching[$controller][] = $action;
            if (!empty($tags)) {
                $this->_tags[$controller][$action] = array();
                foreach ($tags as $tag) {
                    $this->_tags[$controller][$action][] = $tag;
                }
            }
        }
        if ($extension) {
            if (!isset($this->_extensions[$controller])) {
                $this->_extensions[$controller] = array();
            }
            foreach ($actions as $action) {
                $this->_extensions[$controller][$action] = $extension;
            }
        }
    }

    /**
     * Remove a specific page cache static file based on its
     * relative URL from the application's public directory.
     * The file extension is not required here; usually matches
     * the original REQUEST_URI that was cached.
     *
     * @param string $relativeUrl
     * @param bool $recursive
     * @return mixed
     */
    public function removePage($relativeUrl, $recursive = false)
    {
        $cache = $this->getCache(Zend_Cache_Manager::PAGECACHE);
        $encodedCacheId = $this->_encodeCacheId($relativeUrl);

        if ($recursive) {
            $backend = $cache->getBackend();
            if (($backend instanceof Zend_Cache_Backend)
                && method_exists($backend, 'removeRecursively')
            ) {
                $result = $backend->removeRecursively($encodedCacheId);
                if (is_null($result) ) {
                    $result = $backend->removeRecursively($relativeUrl);
                }
                return $result;
            }
        }

        $result = $cache->remove($encodedCacheId);
        if (is_null($result) ) {
            $result = $cache->remove($relativeUrl);
        }
        return $result;
    }

    /**
     * Remove a specific page cache static file based on its
     * relative URL from the application's public directory.
     * The file extension is not required here; usually matches
     * the original REQUEST_URI that was cached.
     *
     * @param array $tags
     * @return mixed
     */
    public function removePagesTagged(array $tags)
    {
        return $this->getCache(Zend_Cache_Manager::PAGECACHE)
            ->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }

    /**
     * Commence page caching for any cacheable actions
     *
     * @return void
     */
    public function preDispatch()
    {
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $stats = ob_get_status(true);
        foreach ($stats as $status) {
            if ($status['name'] == 'Zend_Cache_Frontend_Page::_flush'
            || $status['name'] == 'Zend_Cache_Frontend_Capture::_flush') {
                $obStarted = true;
            }
        }
        if (!isset($obStarted) && isset($this->_caching[$controller]) &&
        in_array($action, $this->_caching[$controller])) {
            $reqUri = $this->getRequest()->getRequestUri();
            $tags = array();
            if (isset($this->_tags[$controller][$action])
            && !empty($this->_tags[$controller][$action])) {
                $tags = array_unique($this->_tags[$controller][$action]);
            }
            $extension = null;
            if (isset($this->_extensions[$controller][$action])) {
                $extension = $this->_extensions[$controller][$action];
            }
            $this->getCache(Zend_Cache_Manager::PAGECACHE)
                ->start($this->_encodeCacheId($reqUri), $tags, $extension);
        }
    }

    /**
     * Encode a Cache ID as hexadecimal. This is a workaround because Backend ID validation
     * is trapped in the Frontend classes. Will try to get this reversed for ZF 2.0
     * because it's a major annoyance to have IDs so restricted!
     *
     * @return string
     * @param string $requestUri
     */
    protected function _encodeCacheId($requestUri)
    {
        return bin2hex($requestUri);
    }

    /**
     * Set an instance of the Cache Manager for this helper
     *
     * @param Zend_Cache_Manager $manager
     * @return void
     */
    public function setManager(Zend_Cache_Manager $manager)
    {
        $this->_manager = $manager;
        return $this;
    }

    /**
     * Get the Cache Manager instance or instantiate the object if not
     * exists. Attempts to load from bootstrap if available.
     *
     * @return Zend_Cache_Manager
     */
    public function getManager()
    {
        if ($this->_manager !== null) {
            return $this->_manager;
        }
        $front = Zend_Controller_Front::getInstance();
        if ($front->getParam('bootstrap')
        && $front->getParam('bootstrap')->getResource('CacheManager')) {
            return $front->getParam('bootstrap')
                ->getResource('CacheManager');
        }
        $this->_manager = new Zend_Cache_Manager;
        return $this->_manager;
    }

    /**
     * Return a list of actions for the current Controller marked for
     * caching
     *
     * @return array
     */
    public function getCacheableActions()
    {
        return $this->_caching;
    }

    /**
     * Return a list of tags set for all cacheable actions
     *
     * @return array
     */
    public function getCacheableTags()
    {
        return $this->_tags;
    }

    /**
     * Proxy non-matched methods back to Zend_Cache_Manager where
     * appropriate
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->getManager(), $method)) {
            return call_user_func_array(
                array($this->getManager(), $method), $args
            );
        }
        throw new Zend_Controller_Action_Exception('Method does not exist:'
            . $method);
    }

}
