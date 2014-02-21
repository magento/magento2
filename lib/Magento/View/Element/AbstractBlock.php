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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Element;

/**
 * Base Content Block class
 *
 * For block generation you must define Data source class, data source class method,
 * parameters array and block template
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractBlock extends \Magento\Object implements BlockInterface
{
    /**
     * Cache group Tag
     */
    const CACHE_GROUP = \Magento\App\Cache\Type\Block::TYPE_IDENTIFIER;

    /**
     * Design
     *
     * @var \Magento\View\DesignInterface
     */
    protected $_design;

    /**
     * Session
     *
     * @var \Magento\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * SID Resolver
     *
     * @var \Magento\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * Translator
     *
     * @var \Magento\TranslateInterface
     */
    protected $_translator;

    /**
     * Block name in layout
     *
     * @var string
     */
    protected $_nameInLayout;

    /**
     * Parent layout of the block
     *
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Request
     *
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Url Builder
     *
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * System event manager
     *
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Application front controller
     *
     * @var \Magento\App\FrontController
     */
    protected $_frontController;

    /**
     * View URL
     *
     * @var \Magento\View\Url
     */
    protected $_viewUrl;

    /**
     * View config model
     *
     * @var \Magento\View\ConfigInterface
     */
    protected $_viewConfig;

    /**
     * Cache State
     *
     * @var \Magento\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * Logger
     *
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Application
     *
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * Escaper
     *
     * @var \Magento\Escaper
     */
    protected $_escaper;

    /**
     * Filter manager
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * Locale
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     */
    protected $_isScopePrivate;

    /**
     * Constructor
     *
     * @param \Magento\View\Element\Context $context
     * @param array $data
     */
    public function __construct(\Magento\View\Element\Context $context, array $data = array())
    {
        $this->_request         = $context->getRequest();
        $this->_layout          = $context->getLayout();
        $this->_eventManager    = $context->getEventManager();
        $this->_urlBuilder      = $context->getUrlBuilder();
        $this->_translator      = $context->getTranslator();
        $this->_cache           = $context->getCache();
        $this->_design          = $context->getDesignPackage();
        $this->_session         = $context->getSession();
        $this->_sidResolver     = $context->getSidResolver();
        $this->_storeConfig     = $context->getStoreConfig();
        $this->_frontController = $context->getFrontController();
        $this->_viewUrl         = $context->getViewUrl();
        $this->_viewConfig      = $context->getViewConfig();
        $this->_cacheState      = $context->getCacheState();
        $this->_logger          = $context->getLogger();
        $this->_app             = $context->getApp();
        $this->_escaper         = $context->getEscaper();
        $this->filterManager    = $context->getFilterManager();
        $this->_locale          = $context->getLocale();
        $this->_isScopePrivate  = false;
        parent::__construct($data);
        $this->_construct();
    }

    /**
     * Get request
     *
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * Please override this one instead of overriding real __construct constructor
     *
     * @return void
     */
    protected function _construct()
    {
        /**
         * Please override this one instead of overriding real __construct constructor
         */
    }

    /**
     * Retrieve parent block
     *
     * @return \Magento\View\Element\AbstractBlock|bool
     */
    public function getParentBlock()
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return false;
        }
        $parentName = $layout->getParentName($this->getNameInLayout());
        if ($parentName) {
            return $layout->getBlock($parentName);
        }
        return false;
    }

    /**
     * Set layout object
     *
     * @param   \Magento\View\LayoutInterface $layout
     * @return  $this
     */
    public function setLayout(\Magento\View\LayoutInterface $layout)
    {
        $this->_layout = $layout;
        $this->_prepareLayout();
        return $this;
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * Retrieve layout object
     *
     * @return \Magento\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Sets/changes name of a block in layout
     *
     * @param string $name
     * @return $this
     */
    public function setNameInLayout($name)
    {
        $layout = $this->getLayout();
        if (!empty($this->_nameInLayout) && $layout) {
            if ($name === $this->_nameInLayout) {
                return $this;
            }
            $layout->renameElement($this->_nameInLayout, $name);
        }
        $this->_nameInLayout = $name;
        return $this;
    }

    /**
     * Retrieves sorted list of child names
     *
     * @return array
     */
    public function getChildNames()
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return array();
        }
        return $layout->getChildNames($this->getNameInLayout());
    }

    /**
     * Set block attribute value
     *
     * Wrapper for method "setData"
     *
     * @param   string $name
     * @param   mixed $value
     * @return  $this
     */
    public function setAttribute($name, $value = null)
    {
        return $this->setData($name, $value);
    }

    /**
     * Set child block
     *
     * @param   string $alias
     * @param   \Magento\View\Element\AbstractBlock|string $block
     * @return  $this
     */
    public function setChild($alias, $block)
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return $this;
        }
        $thisName = $this->getNameInLayout();
        if ($layout->getChildName($thisName, $alias)) {
            $this->unsetChild($alias);
        }
        if ($block instanceof self) {
            $block = $block->getNameInLayout();
        }

        $layout->setChild($thisName, $block, $alias);

        return $this;
    }

    /**
     * Create block with name: {parent}.{alias} and set as child
     *
     * @param string $alias
     * @param string $block
     * @param array $data
     * @return $this new block
     */
    public function addChild($alias, $block, $data = array())
    {
        $block = $this->getLayout()->createBlock($block, $this->getNameInLayout() . '.' . $alias,
            array('data' => $data)
        );
        $this->setChild($alias, $block);
        return $block;
    }

    /**
     * Unset child block
     *
     * @param  string $alias
     * @return $this
     */
    public function unsetChild($alias)
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return $this;
        }
        $layout->unsetChild($this->getNameInLayout(), $alias);
        return $this;
    }

    /**
     * Call a child and unset it, if callback matched result
     *
     * Variable $params will pass to child callback
     * $params may be array, if called from layout with elements with same name, for example:
     * ...<foo>value_1</foo><foo>value_2</foo><foo>value_3</foo>
     *
     * Or, if called like this:
     * ...<foo>value_1</foo><bar>value_2</bar><baz>value_3</baz>
     * - then it will be $params1, $params2, $params3
     *
     * It is no difference anyway, because they will be transformed in appropriate way.
     *
     * @param string $alias
     * @param string $callback
     * @param mixed $result
     * @param array $params
     * @return $this
     */
    public function unsetCallChild($alias, $callback, $result, $params)
    {
        $child = $this->getChildBlock($alias);
        if ($child) {
            $args     = func_get_args();
            $alias    = array_shift($args);
            $callback = array_shift($args);
            $result   = (string)array_shift($args);
            if (!is_array($params)) {
                $params = $args;
            }

            if ($result == call_user_func_array(array(&$child, $callback), $params)) {
                $this->unsetChild($alias);
            }
        }
        return $this;
    }

    /**
     * Unset all children blocks
     *
     * @return $this
     */
    public function unsetChildren()
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return $this;
        }
        $name = $this->getNameInLayout();
        $children = $layout->getChildNames($name);
        foreach ($children as $childName) {
            $layout->unsetChild($name, $childName);
        }
        return $this;
    }

    /**
     * Retrieve child block by name
     *
     * @param string $alias
     * @return \Magento\View\Element\AbstractBlock|bool
     */
    public function getChildBlock($alias)
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return false;
        }
        $name = $layout->getChildName($this->getNameInLayout(), $alias);
        if ($name) {
            return $layout->getBlock($name);
        }
        return false;
    }

    /**
     * Retrieve child block HTML
     *
     * @param   string $alias
     * @param   boolean $useCache
     * @return  string
     */
    public function getChildHtml($alias = '', $useCache = true)
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return '';
        }
        $name = $this->getNameInLayout();
        $out = '';
        if ($alias) {
            $childName = $layout->getChildName($name, $alias);
            if ($childName) {
                $out = $layout->renderElement($childName, $useCache);
            }
        } else {
            foreach ($layout->getChildNames($name) as $child) {
                $out .= $layout->renderElement($child, $useCache);
            }
        }

        return $out;
    }

    /**
     * Render output of child child element
     *
     * @param string $alias
     * @param string $childChildAlias
     * @param bool $useCache
     * @return string
     */
    public function getChildChildHtml($alias, $childChildAlias = '', $useCache = true)
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return '';
        }
        $childName = $layout->getChildName($this->getNameInLayout(), $alias);
        if (!$childName) {
            return '';
        }
        $out = '';
        if ($childChildAlias) {
            $childChildName = $layout->getChildName($childName, $childChildAlias);
            $out = $layout->renderElement($childChildName, $useCache);
        } else {
            foreach ($layout->getChildNames($childName) as $childChild) {
                $out .= $layout->renderElement($childChild, $useCache);
            }
        }
        return $out;
    }

    /**
     * Retrieve block html
     *
     * @param   string $name
     * @return  string
     */
    public function getBlockHtml($name)
    {
        $block = $this->_layout->getBlock($name);
        if ($block) {
            return $block->toHtml();
        }
        return '';
    }

    /**
     * Insert child element into specified position
     *
     * By default inserts as first element into children list
     *
     * @param \Magento\View\Element\AbstractBlock|string $element
     * @param string|int|null $siblingName
     * @param bool $after
     * @param string $alias
     * @return $this|bool
     */
    public function insert($element, $siblingName = 0, $after = true, $alias = '')
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return false;
        }
        if ($element instanceof \Magento\View\Element\AbstractBlock) {
            $elementName = $element->getNameInLayout();
        } else {
            $elementName = $element;
        }
        $layout->setChild($this->_nameInLayout, $elementName, $alias);
        $layout->reorderChild($this->_nameInLayout, $elementName, $siblingName, $after);
        return $this;
    }

    /**
     * Append element to the end of children list
     *
     * @param \Magento\View\Element\AbstractBlock|string $element
     * @param string $alias
     * @return $this
     */
    public function append($element, $alias = '')
    {
        return $this->insert($element, null, true, $alias);
    }

    /**
     * Get a group of child blocks
     *
     * Returns an array of <alias> => <block>
     * or an array of <alias> => <callback_result>
     * The callback currently supports only $this methods and passes the alias as parameter
     *
     * @param string $groupName
     * @return array
     */
    public function getGroupChildNames($groupName)
    {
        return $this->getLayout()->getGroupChildNames($this->getNameInLayout(), $groupName);
    }

    /**
     * Get a value from child block by specified key
     *
     * @param string $alias
     * @param string $key
     * @return mixed
     */
    public function getChildData($alias, $key = '')
    {
        $child = $this->getChildBlock($alias);
        if ($child) {
            return $child->getData($key);
        }
        return null;
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return $this;
    }

    /**
     * Produce and return block's html output
     *
     * This method should not be overridden. You can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    public function toHtml()
    {
        $this->_eventManager->dispatch('view_block_abstract_to_html_before', array('block' => $this));
        if ($this->_storeConfig->getConfig('advanced/modules_disable_output/' . $this->getModuleName())) {
            return '';
        }

        $html = $this->_loadCache();
        if ($html === false) {
            if ($this->hasData('translate_inline')) {
                $this->_translator->setTranslateInline($this->getData('translate_inline'));
            }

            $this->_beforeToHtml();
            $html = $this->_toHtml();
            $this->_saveCache($html);

            if ($this->hasData('translate_inline')) {
                $this->_translator->setTranslateInline(true);
            }
        }
        $html = $this->_afterToHtml($html);

        return $html;
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        return $html;
    }

    /**
     * Override this method in descendants to produce html
     *
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }

    /**
     * Retrieve data-ui-id attribute
     *
     * Retrieve data-ui-id attribute which will distinguish
     * link/input/container/anything else in template among others.
     * Function takes an arbitrary amount of parameters.
     *
     * @return string
     */
    public function getUiId()
    {
        return ' data-ui-id="' . call_user_func_array(array($this, 'getJsId'), func_get_args()) . '" ';
    }

    /**
     * Generate id for using in JavaScript UI
     *
     * Function takes an arbitrary amount of parameters
     *
     * @return string
     */
    public function getJsId()
    {
        $rawId = $this->_nameInLayout . '-' . implode('-', func_get_args());
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($rawId)), '-');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = array())
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieve url of themes file
     *
     * @param string $file path to file in theme
     * @param array $params
     * @return string
     */

    public function getViewFileUrl($file = null, array $params = array())
    {
        try {
            $params = array_merge(['_secure' => $this->getRequest()->isSecure()], $params);
            return $this->_viewUrl->getViewFileUrl($file, $params);
        } catch (\Magento\Exception $e) {
            $this->_logger->logException($e);
            return $this->_getNotFoundUrl();
        }
    }

    /**
     * Get 404 file not found url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getNotFoundUrl($route = '', $params = array('_direct' => 'core/index/notfound'))
    {
        return $this->getUrl($route, $params);
    }

    /**
     * Retrieve formatting date
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate(
        $date = null, $format =  \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT, $showTime = false
    ) {
        return $this->_locale->formatDate($date, $format, $showTime);
    }

    /**
     * Retrieve formatting time
     *
     * @param   string $time
     * @param   string $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime(
        $time = null, $format = \Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT, $showDate = false
    ) {
        return $this->_locale->formatTime($time, $format, $showDate);
    }

    /**
     * Retrieve module name of block
     *
     * @return string
     */
    public function getModuleName()
    {
        if (!$this->_getData('module_name')) {
            $this->setData('module_name', self::extractModuleName(get_class($this)));
        }
        return $this->_getData('module_name');
    }

    /**
     * Extract module name from specified block class name
     *
     * @param string $className
     * @return string
     */
    public static function extractModuleName($className)
    {
        $namespace = substr($className, 0, strpos($className, \Magento\Autoload\IncludePath::NS_SEPARATOR . 'Block'));
        return str_replace(\Magento\Autoload\IncludePath::NS_SEPARATOR, '_', $namespace);
    }

    /**
     * Escape html entities
     *
     * @param   string|array $data
     * @param   array|null $allowedTags
     * @return  string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->_escaper->escapeHtml($data, $allowedTags);
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string|null $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        return $this->filterManager->stripTags($data, array(
            'allowableTags' => $allowableTags,
            'escape'        => $allowHtmlEntities
        ));
    }

    /**
     * Escape html entities in url
     *
     * @param string $data
     * @return string
     */
    public function escapeUrl($data)
    {
        return $this->_escaper->escapeUrl($data);
    }

    /**
     * Escape quotes inside html attributes
     *
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param  string $data
     * @param  bool $addSlashes
     * @return string
     */
    public function escapeQuote($data, $addSlashes = false)
    {
        return $this->_escaper->escapeQuote($data, $addSlashes);
    }

    /**
     * Escape quotes in java scripts
     *
     * @param string|array $data
     * @param string $quote
     * @return string|array
     */
    public function escapeJsQuote($data, $quote = '\'')
    {
        return $this->_escaper->escapeJsQuote($data, $quote);
    }

    /**
     * Get block name
     *
     * @return string
     */
    public function getNameInLayout()
    {
        return $this->_nameInLayout;
    }

    /**
     * Get cache key informative items
     *
     * Provide string array key to share specific info item with FPC placeholder
     *
     * @return string[]
     */
    public function getCacheKeyInfo()
    {
        return array(
            $this->getNameInLayout()
        );
    }

    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        if ($this->hasData('cache_key')) {
            return $this->getData('cache_key');
        }
        /**
         * don't prevent recalculation by saving generated cache key
         * because of ability to render single block instance with different data
         */
        $key = $this->getCacheKeyInfo();
        //ksort($key);  // ignore order
        $key = array_values($key);  // ignore array keys
        $key = implode('|', $key);
        $key = sha1($key);
        return $key;
    }

    /**
     * Get tags array for saving cache
     *
     * @return array
     */
    protected function getCacheTags()
    {
        if (!$this->hasData('cache_tags')) {
            $tags = array();
        } else {
            $tags = $this->getData('cache_tags');
        }
        $tags[] = self::CACHE_GROUP;
        return $tags;
    }

    /**
     * Get block cache life time
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return null;
        }
        return $this->getData('cache_lifetime');
    }

    /**
     * Load block html from cache storage
     *
     * @return string|false
     */
    protected function _loadCache()
    {
        if (is_null($this->getCacheLifetime()) || !$this->_cacheState->isEnabled(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $this->getCacheKey();
        $cacheData = $this->_cache->load($cacheKey);
        if ($cacheData) {
            $cacheData = str_replace(
                $this->_getSidPlaceholder($cacheKey),
                $this->_sidResolver->getSessionIdQueryParam($this->_session) . '=' . $this->_session->getSessionId(),
                $cacheData
            );
        }
        return $cacheData;
    }

    /**
     * Save block content to cache storage
     *
     * @param string $data
     * @return $this
     */
    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime()) || !$this->_cacheState->isEnabled(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $this->getCacheKey();
        $data = str_replace(
            $this->_sidResolver->getSessionIdQueryParam($this->_session) . '=' . $this->_session->getSessionId(),
            $this->_getSidPlaceholder($cacheKey),
            $data
        );

        $this->_cache->save($data, $cacheKey, $this->getCacheTags(), $this->getCacheLifetime());
        return $this;
    }

    /**
     * Get SID placeholder for cache
     *
     * @param null|string $cacheKey
     * @return string
     */
    protected function _getSidPlaceholder($cacheKey = null)
    {
        if (is_null($cacheKey)) {
            $cacheKey = $this->getCacheKey();
        }

        return '<!--SID=' . $cacheKey . '-->';
    }

    /**
     * Get variable value from view configuration
     *
     * Module name can be omitted. If omitted, it will be determined automatically.
     *
     * @param string $name variable name
     * @param string|null $module optional module name
     * @return string|false
     */
    public function getVar($name, $module = null)
    {
        $module = $module ?: $this->getModuleName();
        return $this->_viewConfig->getViewConfig()->getVarValue($module, $name);
    }

    /**
     * Determine if the block scope is private or public.
     * Returns true if scope is private, false otherwise
     *
     * @return bool
     */
    public function isScopePrivate()
    {
        return $this->_isScopePrivate;
    }
}
