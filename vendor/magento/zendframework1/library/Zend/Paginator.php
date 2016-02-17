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
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Loader_PluginLoader
 */
#require_once 'Zend/Loader/PluginLoader.php';

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Paginator implements Countable, IteratorAggregate
{
    /**
     * Specifies that the factory should try to detect the proper adapter type first
     *
     * @var string
     */
    const INTERNAL_ADAPTER = 'Zend_Paginator_Adapter_Internal';

    /**
     * The cache tag prefix used to namespace Paginator results in the cache
     *
     */
    const CACHE_TAG_PREFIX = 'Zend_Paginator_';

    /**
     * Adapter plugin loader
     *
     * @var Zend_Loader_PluginLoader
     */
    protected static $_adapterLoader = null;

    /**
     * Configuration file
     *
     * @var Zend_Config
     */
    protected static $_config = null;

    /**
     * Default scrolling style
     *
     * @var string
     */
    protected static $_defaultScrollingStyle = 'Sliding';

    /**
     * Default item count per page
     *
     * @var int
     */
    protected static $_defaultItemCountPerPage = 10;

    /**
     * Default number of local pages (i.e., the number of discretes
     * page numbers that will be displayed, including the current
     * page number)
     *
     * @var int
     */
    protected static $_defaultPageRange = 10;

    /**
     * Scrolling style plugin loader
     *
     * @var Zend_Loader_PluginLoader
     */
    protected static $_scrollingStyleLoader = null;

    /**
     * Cache object
     *
     * @var Zend_Cache_Core
     */
    protected static $_cache;

    /**
     * Enable or disable the cache by Zend_Paginator instance
     *
     * @var bool
     */
    protected $_cacheEnabled = true;

    /**
     * Adapter
     *
     * @var Zend_Paginator_Adapter_Interface
     */
    protected $_adapter = null;

    /**
     * Number of items in the current page
     *
     * @var integer
     */
    protected $_currentItemCount = null;

    /**
     * Current page items
     *
     * @var Traversable
     */
    protected $_currentItems = null;

    /**
     * Current page number (starting from 1)
     *
     * @var integer
     */
    protected $_currentPageNumber = 1;

    /**
     * Result filter
     *
     * @var Zend_Filter_Interface
     */
    protected $_filter = null;

    /**
     * Number of items per page
     *
     * @var integer
     */
    protected $_itemCountPerPage = null;

    /**
     * Number of pages
     *
     * @var integer
     */
    protected $_pageCount = null;

    /**
     * Number of local pages (i.e., the number of discrete page numbers
     * that will be displayed, including the current page number)
     *
     * @var integer
     */
    protected $_pageRange = null;

    /**
     * Pages
     *
     * @var array
     */
    protected $_pages = null;

    /**
     * View instance used for self rendering
     *
     * @var Zend_View_Interface
     */
    protected $_view = null;

    /**
     * Adds an adapter prefix path to the plugin loader.
     *
     * @param string $prefix
     * @param string $path
     */
    public static function addAdapterPrefixPath($prefix, $path)
    {
        self::getAdapterLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * Adds an array of adapter prefix paths to the plugin
     * loader.
     *
     * <code>
     * $prefixPaths = array(
     *     'My_Paginator_Adapter'   => 'My/Paginator/Adapter/',
     *     'Your_Paginator_Adapter' => 'Your/Paginator/Adapter/'
     * );
     * </code>
     *
     * @param array $prefixPaths
     */
    public static function addAdapterPrefixPaths(array $prefixPaths)
    {
        if (isset($prefixPaths['prefix']) && isset($prefixPaths['path'])) {
            self::addAdapterPrefixPath($prefixPaths['prefix'], $prefixPaths['path']);
        } else {
            foreach ($prefixPaths as $prefix => $path) {
                if (is_array($path) && isset($path['prefix']) && isset($path['path'])) {
                    $prefix = $path['prefix'];
                    $path   = $path['path'];
                }

                self::addAdapterPrefixPath($prefix, $path);
            }
        }
    }

    /**
     * Adds a scrolling style prefix path to the plugin loader.
     *
     * @param string $prefix
     * @param string $path
     */
    public static function addScrollingStylePrefixPath($prefix, $path)
    {
        self::getScrollingStyleLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * Adds an array of scrolling style prefix paths to the plugin
     * loader.
     *
     * <code>
     * $prefixPaths = array(
     *     'My_Paginator_ScrollingStyle'   => 'My/Paginator/ScrollingStyle/',
     *     'Your_Paginator_ScrollingStyle' => 'Your/Paginator/ScrollingStyle/'
     * );
     * </code>
     *
     * @param array $prefixPaths
     */
    public static function addScrollingStylePrefixPaths(array $prefixPaths)
    {
        if (isset($prefixPaths['prefix']) && isset($prefixPaths['path'])) {
            self::addScrollingStylePrefixPath($prefixPaths['prefix'], $prefixPaths['path']);
        } else {
            foreach ($prefixPaths as $prefix => $path) {
                if (is_array($path) && isset($path['prefix']) && isset($path['path'])) {
                    $prefix = $path['prefix'];
                    $path   = $path['path'];
                }

                self::addScrollingStylePrefixPath($prefix, $path);
            }
        }
    }

    /**
     * Factory.
     *
     * @param  mixed $data
     * @param  string $adapter
     * @param  array $prefixPaths
     * @return Zend_Paginator
     */
    public static function factory($data, $adapter = self::INTERNAL_ADAPTER,
                                   array $prefixPaths = null)
    {
        if ($data instanceof Zend_Paginator_AdapterAggregate) {
            return new self($data->getPaginatorAdapter());
        } else {
            if ($adapter == self::INTERNAL_ADAPTER) {
                if (is_array($data)) {
                    $adapter = 'Array';
                } else if ($data instanceof Zend_Db_Table_Select) {
                    $adapter = 'DbTableSelect';
                } else if ($data instanceof Zend_Db_Select) {
                    $adapter = 'DbSelect';
                } else if ($data instanceof Iterator) {
                    $adapter = 'Iterator';
                } else if (is_integer($data)) {
                    $adapter = 'Null';
                } else {
                    $type = (is_object($data)) ? get_class($data) : gettype($data);

                    /**
                     * @see Zend_Paginator_Exception
                     */
                    #require_once 'Zend/Paginator/Exception.php';

                    throw new Zend_Paginator_Exception('No adapter for type ' . $type);
                }
            }

            $pluginLoader = self::getAdapterLoader();

            if (null !== $prefixPaths) {
                foreach ($prefixPaths as $prefix => $path) {
                    $pluginLoader->addPrefixPath($prefix, $path);
                }
            }

            $adapterClassName = $pluginLoader->load($adapter);

            return new self(new $adapterClassName($data));
        }
    }

    /**
     * Returns the adapter loader.  If it doesn't exist it's created.
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getAdapterLoader()
    {
        if (self::$_adapterLoader === null) {
            self::$_adapterLoader = new Zend_Loader_PluginLoader(
                array('Zend_Paginator_Adapter' => 'Zend/Paginator/Adapter')
            );
        }

        return self::$_adapterLoader;
    }

    /**
     * Set a global config
     *
     * @param Zend_Config $config
     */
    public static function setConfig(Zend_Config $config)
    {
        self::$_config = $config;

        $adapterPaths = $config->get('adapterpaths');

        if ($adapterPaths != null) {
            self::addAdapterPrefixPaths($adapterPaths->adapterpath->toArray());
        }

        $prefixPaths = $config->get('prefixpaths');

        if ($prefixPaths != null) {
            self::addScrollingStylePrefixPaths($prefixPaths->prefixpath->toArray());
        }

        $scrollingStyle = $config->get('scrollingstyle');

        if ($scrollingStyle != null) {
            self::setDefaultScrollingStyle($scrollingStyle);
        }
    }

    /**
     * Returns the default scrolling style.
     *
     * @return  string
     */
    public static function getDefaultScrollingStyle()
    {
        return self::$_defaultScrollingStyle;
    }

    /**
     * Get the default item count per page
     *
     * @return int
     */
    public static function getDefaultItemCountPerPage()
    {
        return self::$_defaultItemCountPerPage;
    }

    /**
     * Set the default item count per page
     *
     * @param int $count
     */
    public static function setDefaultItemCountPerPage($count)
    {
        self::$_defaultItemCountPerPage = (int) $count;
    }

    /**
     * Get the default page range
     *
     * @return int
     */
    public static function getDefaultPageRange()
    {
        return self::$_defaultPageRange;
    }

    /**
     * Set the default page range
     *
     * @param int $count
     */
    public static function setDefaultPageRange($count)
    {
        self::$_defaultPageRange = (int) $count;
    }

    /**
     * Sets a cache object
     *
     * @param Zend_Cache_Core $cache
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$_cache = $cache;
    }

    /**
     * Sets the default scrolling style.
     *
     * @param  string $scrollingStyle
     */
    public static function setDefaultScrollingStyle($scrollingStyle = 'Sliding')
    {
        self::$_defaultScrollingStyle = $scrollingStyle;
    }

    /**
     * Returns the scrolling style loader.  If it doesn't exist it's
     * created.
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getScrollingStyleLoader()
    {
        if (self::$_scrollingStyleLoader === null) {
            self::$_scrollingStyleLoader = new Zend_Loader_PluginLoader(
                array('Zend_Paginator_ScrollingStyle' => 'Zend/Paginator/ScrollingStyle')
            );
        }

        return self::$_scrollingStyleLoader;
    }

    /**
     * Constructor.
     *
     * @param Zend_Paginator_Adapter_Interface|Zend_Paginator_AdapterAggregate $adapter
     */
    public function __construct($adapter)
    {
        if ($adapter instanceof Zend_Paginator_Adapter_Interface) {
            $this->_adapter = $adapter;
        } else if ($adapter instanceof Zend_Paginator_AdapterAggregate) {
            $this->_adapter = $adapter->getPaginatorAdapter();
        } else {
            /**
             * @see Zend_Paginator_Exception
             */
            #require_once 'Zend/Paginator/Exception.php';

            throw new Zend_Paginator_Exception(
                'Zend_Paginator only accepts instances of the type ' .
                'Zend_Paginator_Adapter_Interface or Zend_Paginator_AdapterAggregate.'
            );
        }

        $config = self::$_config;

        if ($config != null) {
            $setupMethods = array('ItemCountPerPage', 'PageRange');

            foreach ($setupMethods as $setupMethod) {
                $value = $config->get(strtolower($setupMethod));

                if ($value != null) {
                    $setupMethod = 'set' . $setupMethod;
                    $this->$setupMethod($value);
                }
            }
        }
    }

    /**
     * Serializes the object as a string.  Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return '';
    }

    /**
     * Enables/Disables the cache for this instance
     *
     * @param bool $enable
     * @return Zend_Paginator
     */
    public function setCacheEnabled($enable)
    {
        $this->_cacheEnabled = (bool)$enable;
        return $this;
    }

    /**
     * Returns the number of pages.
     *
     * @return integer
     */
    public function count()
    {
        if (!$this->_pageCount) {
            $this->_pageCount = $this->_calculatePageCount();
        }

        return $this->_pageCount;
    }

    /**
     * Returns the total number of items available.  Uses cache if caching is enabled.
     *
     * @return integer
     */
    public function getTotalItemCount()
    {
        if (!$this->_cacheEnabled()) {
            return count($this->getAdapter());
        } else {
            $cacheId   = md5($this->_getCacheInternalId(). '_itemCount');
            $itemCount = self::$_cache->load($cacheId);

            if ($itemCount === false) {
                $itemCount = count($this->getAdapter());

                self::$_cache->save($itemCount, $cacheId, array($this->_getCacheInternalId()));
            }

            return $itemCount;
        }
    }

    /**
     * Clear the page item cache.
     *
     * @param int $pageNumber
     * @return Zend_Paginator
     */
    public function clearPageItemCache($pageNumber = null)
    {
        if (!$this->_cacheEnabled()) {
            return $this;
        }

        if (null === $pageNumber) {
            foreach (self::$_cache->getIdsMatchingTags(array($this->_getCacheInternalId())) as $id) {
                if (preg_match('|'.self::CACHE_TAG_PREFIX."(\d+)_.*|", $id, $page)) {
                    self::$_cache->remove($this->_getCacheId($page[1]));
                }
            }
        } else {
            $cleanId = $this->_getCacheId($pageNumber);
            self::$_cache->remove($cleanId);
        }
        return $this;
    }

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param  integer $relativeItemNumber Relative item number
     * @param  integer $pageNumber Page number
     * @return integer
     */
    public function getAbsoluteItemNumber($relativeItemNumber, $pageNumber = null)
    {
        $relativeItemNumber = $this->normalizeItemNumber($relativeItemNumber);

        if ($pageNumber == null) {
            $pageNumber = $this->getCurrentPageNumber();
        }

        $pageNumber = $this->normalizePageNumber($pageNumber);

        return (($pageNumber - 1) * $this->getItemCountPerPage()) + $relativeItemNumber;
    }

    /**
     * Returns the adapter.
     *
     * @return Zend_Paginator_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Returns the number of items for the current page.
     *
     * @return integer
     */
    public function getCurrentItemCount()
    {
        if ($this->_currentItemCount === null) {
            $this->_currentItemCount = $this->getItemCount($this->getCurrentItems());
        }

        return $this->_currentItemCount;
    }

    /**
     * Returns the items for the current page.
     *
     * @return Traversable
     */
    public function getCurrentItems()
    {
        if ($this->_currentItems === null) {
            $this->_currentItems = $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->_currentItems;
    }

    /**
     * Returns the current page number.
     *
     * @return integer
     */
    public function getCurrentPageNumber()
    {
        return $this->normalizePageNumber($this->_currentPageNumber);
    }

    /**
     * Sets the current page number.
     *
     * @param  integer $pageNumber Page number
     * @return Zend_Paginator $this
     */
    public function setCurrentPageNumber($pageNumber)
    {
        $this->_currentPageNumber = (integer) $pageNumber;
        $this->_currentItems      = null;
        $this->_currentItemCount  = null;

        return $this;
    }

    /**
     * Get the filter
     *
     * @return Zend_Filter_Interface
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Set a filter chain
     *
     * @param Zend_Filter_Interface $filter
     * @return Zend_Paginator
     */
    public function setFilter(Zend_Filter_Interface $filter)
    {
        $this->_filter = $filter;

        return $this;
    }

    /**
     * Returns an item from a page.  The current page is used if there's no
     * page sepcified.
     *
     * @param  integer $itemNumber Item number (1 to itemCountPerPage)
     * @param  integer $pageNumber
     * @return mixed
     */
    public function getItem($itemNumber, $pageNumber = null)
    {
        if ($pageNumber == null) {
            $pageNumber = $this->getCurrentPageNumber();
        } else if ($pageNumber < 0) {
            $pageNumber = ($this->count() + 1) + $pageNumber;
        }

        $page = $this->getItemsByPage($pageNumber);
        $itemCount = $this->getItemCount($page);

        if ($itemCount == 0) {
            /**
             * @see Zend_Paginator_Exception
             */
            #require_once 'Zend/Paginator/Exception.php';

            throw new Zend_Paginator_Exception('Page ' . $pageNumber . ' does not exist');
        }

        if ($itemNumber < 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            /**
             * @see Zend_Paginator_Exception
             */
            #require_once 'Zend/Paginator/Exception.php';

            throw new Zend_Paginator_Exception('Page ' . $pageNumber . ' does not'
                                             . ' contain item number ' . $itemNumber);
        }

        return $page[$itemNumber - 1];
    }

    /**
     * Returns the number of items per page.
     *
     * @return integer
     */
    public function getItemCountPerPage()
    {
        if (empty($this->_itemCountPerPage)) {
            $this->_itemCountPerPage = self::getDefaultItemCountPerPage();
        }

        return $this->_itemCountPerPage;
    }

    /**
     * Sets the number of items per page.
     *
     * @param  integer $itemCountPerPage
     * @return Zend_Paginator $this
     */
    public function setItemCountPerPage($itemCountPerPage = -1)
    {
        $this->_itemCountPerPage = (integer) $itemCountPerPage;
        if ($this->_itemCountPerPage < 1) {
            $this->_itemCountPerPage = $this->getTotalItemCount();
        }
        $this->_pageCount        = $this->_calculatePageCount();
        $this->_currentItems     = null;
        $this->_currentItemCount = null;

        return $this;
    }

    /**
     * Returns the number of items in a collection.
     *
     * @param  mixed $items Items
     * @return integer
     */
    public function getItemCount($items)
    {
        $itemCount = 0;

        if (is_array($items) || $items instanceof Countable) {
            $itemCount = count($items);
        } else { // $items is something like LimitIterator
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     * Returns the items for a given page.
     *
     * @return Traversable
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        if ($this->_cacheEnabled()) {
            $data = self::$_cache->load($this->_getCacheId($pageNumber));
            if ($data !== false) {
                return $data;
            }
        }

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->_adapter->getItems($offset, $this->getItemCountPerPage());

        $filter = $this->getFilter();

        if ($filter !== null) {
            $items = $filter->filter($items);
        }

        if (!$items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        if ($this->_cacheEnabled()) {
            self::$_cache->save($items, $this->_getCacheId($pageNumber), array($this->_getCacheInternalId()));
        }

        return $items;
    }

    /**
     * Returns a foreach-compatible iterator.
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return $this->getCurrentItems();
    }

    /**
     * Returns the page range (see property declaration above).
     *
     * @return integer
     */
    public function getPageRange()
    {
        if (null === $this->_pageRange) {
            $this->_pageRange = self::getDefaultPageRange();
        }

        return $this->_pageRange;
    }

    /**
     * Sets the page range (see property declaration above).
     *
     * @param  integer $pageRange
     * @return Zend_Paginator $this
     */
    public function setPageRange($pageRange)
    {
        $this->_pageRange = (integer) $pageRange;

        return $this;
    }

    /**
     * Returns the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return array
     */
    public function getPages($scrollingStyle = null)
    {
        if ($this->_pages === null) {
            $this->_pages = $this->_createPages($scrollingStyle);
        }

        return $this->_pages;
    }

    /**
     * Returns a subset of pages within a given range.
     *
     * @param  integer $lowerBound Lower bound of the range
     * @param  integer $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound)
    {
        $lowerBound = $this->normalizePageNumber($lowerBound);
        $upperBound = $this->normalizePageNumber($upperBound);

        $pages = array();

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = $pageNumber;
        }

        return $pages;
    }

    /**
     * Returns the page item cache.
     *
     * @return array
     */
    public function getPageItemCache()
    {
        $data = array();
        if ($this->_cacheEnabled()) {
            foreach (self::$_cache->getIdsMatchingTags(array($this->_getCacheInternalId())) as $id) {
                    if (preg_match('|'.self::CACHE_TAG_PREFIX."(\d+)_.*|", $id, $page)) {
                        $data[$page[1]] = self::$_cache->load($this->_getCacheId($page[1]));
                    }
            }
        }
        return $data;
    }

    /**
     * Retrieves the view instance.  If none registered, attempts to pull f
     * rom ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if ($this->_view === null) {
            /**
             * @see Zend_Controller_Action_HelperBroker
             */
            #require_once 'Zend/Controller/Action/HelperBroker.php';

            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if ($viewRenderer->view === null) {
                $viewRenderer->initView();
            }
            $this->_view = $viewRenderer->view;
        }

        return $this->_view;
    }

    /**
     * Sets the view object.
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Paginator
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;

        return $this;
    }

    /**
     * Brings the item number in range of the page.
     *
     * @param  integer $itemNumber
     * @return integer
     */
    public function normalizeItemNumber($itemNumber)
    {
        $itemNumber = (integer) $itemNumber;

        if ($itemNumber < 1) {
            $itemNumber = 1;
        }

        if ($itemNumber > $this->getItemCountPerPage()) {
            $itemNumber = $this->getItemCountPerPage();
        }

        return $itemNumber;
    }

    /**
     * Brings the page number in range of the paginator.
     *
     * @param  integer $pageNumber
     * @return integer
     */
    public function normalizePageNumber($pageNumber)
    {
        $pageNumber = (integer) $pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->count();

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * Renders the paginator.
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $view = $this->getView();

        return $view->paginationControl($this);
    }

    /**
     * Returns the items of the current page as JSON.
     *
     * @return string
     */
    public function toJson()
    {
        $currentItems = $this->getCurrentItems();

        if ($currentItems instanceof Zend_Db_Table_Rowset_Abstract) {
            return Zend_Json::encode($currentItems->toArray());
        } else {
            return Zend_Json::encode($currentItems);
        }
    }

    /**
     * Tells if there is an active cache object
     * and if the cache has not been desabled
     *
     * @return bool
     */
    protected function _cacheEnabled()
    {
        return ((self::$_cache !== null) && $this->_cacheEnabled);
    }

    /**
     * Makes an Id for the cache
     * Depends on the adapter object and the page number
     *
     * Used to store item in cache from that Paginator instance
     *  and that current page
     *
     * @param int $page
     * @return string
     */
    protected function _getCacheId($page = null)
    {
        if ($page === null) {
            $page = $this->getCurrentPageNumber();
        }
        return self::CACHE_TAG_PREFIX . $page . '_' . $this->_getCacheInternalId();
    }

    /**
     * Get the internal cache id
     * Depends on the adapter and the item count per page
     *
     * Used to tag that unique Paginator instance in cache
     *
     * @return string
     */
    protected function _getCacheInternalId()
    {
        $adapter = $this->getAdapter();

        if (method_exists($adapter, 'getCacheIdentifier')) {
            return md5(serialize(array(
                $adapter->getCacheIdentifier(), $this->getItemCountPerPage()
            )));
        } else {
            return md5(serialize(array(
                $adapter,
                $this->getItemCountPerPage()
            )));
        }
    }

    /**
     * Calculates the page count.
     *
     * @return integer
     */
    protected function _calculatePageCount()
    {
        return (integer) ceil($this->getTotalItemCount() / $this->getItemCountPerPage());
    }

    /**
     * Creates the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return stdClass
     */
    protected function _createPages($scrollingStyle = null)
    {
        $pageCount         = $this->count();
        $currentPageNumber = $this->getCurrentPageNumber();

        $pages = new stdClass();
        $pages->pageCount        = $pageCount;
        $pages->itemCountPerPage = $this->getItemCountPerPage();
        $pages->first            = 1;
        $pages->current          = $currentPageNumber;
        $pages->last             = $pageCount;

        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $pages->previous = $currentPageNumber - 1;
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $pages->next = $currentPageNumber + 1;
        }

        // Pages in range
        $scrollingStyle = $this->_loadScrollingStyle($scrollingStyle);
        $pages->pagesInRange     = $scrollingStyle->getPages($this);
        $pages->firstPageInRange = min($pages->pagesInRange);
        $pages->lastPageInRange  = max($pages->pagesInRange);

        // Item numbers
        if ($this->getCurrentItems() !== null) {
            $pages->currentItemCount = $this->getCurrentItemCount();
            $pages->itemCountPerPage = $this->getItemCountPerPage();
            $pages->totalItemCount   = $this->getTotalItemCount();
            $pages->firstItemNumber  = (($currentPageNumber - 1) * $this->getItemCountPerPage()) + 1;
            $pages->lastItemNumber   = $pages->firstItemNumber + $pages->currentItemCount - 1;
        }

        return $pages;
    }

    /**
     * Loads a scrolling style.
     *
     * @param string $scrollingStyle
     * @return Zend_Paginator_ScrollingStyle_Interface
     */
    protected function _loadScrollingStyle($scrollingStyle = null)
    {
        if ($scrollingStyle === null) {
            $scrollingStyle = self::$_defaultScrollingStyle;
        }

        switch (strtolower(gettype($scrollingStyle))) {
            case 'object':
                if (!$scrollingStyle instanceof Zend_Paginator_ScrollingStyle_Interface) {
                    /**
                     * @see Zend_View_Exception
                     */
                    #require_once 'Zend/View/Exception.php';

                    throw new Zend_View_Exception('Scrolling style must implement ' .
                        'Zend_Paginator_ScrollingStyle_Interface');
                }

                return $scrollingStyle;

            case 'string':
                $className = self::getScrollingStyleLoader()->load($scrollingStyle);

                return new $className();

            case 'null':
                // Fall through to default case

            default:
                /**
                 * @see Zend_View_Exception
                 */
                #require_once 'Zend/View/Exception.php';

                throw new Zend_View_Exception('Scrolling style must be a class ' .
                    'name or object implementing Zend_Paginator_ScrollingStyle_Interface');
        }
    }
}
