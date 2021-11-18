<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element;

use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Base class for all blocks.
 *
 * Avoid inheriting from this class. Will be deprecated.
 *
 * Marked as public API because it is actively used now.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
abstract class AbstractBlock extends \Magento\Framework\DataObject implements BlockInterface
{
    /**
     * Cache group Tag
     */
    const CACHE_GROUP = \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER;

    /**
     * Prefix for cache key of block
     */
    const CACHE_KEY_PREFIX = 'BLOCK_';

    /**
     * Design
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * Session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * SID Resolver
     *
     * @var \Magento\Framework\Session\SidResolverInterface
     * @deprecated 102.0.5 Not used anymore.
     */
    protected $_sidResolver;

    /**
     * Block name in layout
     *
     * @var string
     */
    protected $_nameInLayout;

    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * JS layout configuration
     *
     * @var array
     */
    protected $jsLayout = [];

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * System event manager
     *
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Application front controller
     *
     * @var \Magento\Framework\App\FrontController
     */
    protected $_frontController;

    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * View config model
     *
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $_viewConfig;

    /**
     * Cache State
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @see https://devdocs.magento.com/guides/v2.4/extension-dev-guide/cache/page-caching/private-content.html
     * @var bool
     * @deprecated
     * @since 103.0.1
     */
    protected $_isScopePrivate = false;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     * @since 101.0.0
     */
    protected $_cache;

    /**
     * @var LockGuardedCacheLoader
     */
    private $lockQuery;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        $this->_request = $context->getRequest();
        $this->_layout = $context->getLayout();
        $this->_eventManager = $context->getEventManager();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_cache = $context->getCache();
        $this->_design = $context->getDesignPackage();
        $this->_session = $context->getSession();
        $this->_sidResolver = $context->getSidResolver();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_assetRepo = $context->getAssetRepository();
        $this->_viewConfig = $context->getViewConfig();
        $this->_cacheState = $context->getCacheState();
        $this->_logger = $context->getLogger();
        $this->_escaper = $context->getEscaper();
        $this->filterManager = $context->getFilterManager();
        $this->_localeDate = $context->getLocaleDate();
        $this->inlineTranslation = $context->getInlineTranslation();
        $this->lockQuery = $context->getLockGuardedCacheLoader();
        if (isset($data['jsLayout'])) {
            $this->jsLayout = $data['jsLayout'];
            unset($data['jsLayout']);
        }
        parent::__construct($data);
        $this->_construct();
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        return json_encode($this->jsLayout);
    }

    /**
     * Get request
     *
     * @return \Magento\Framework\App\RequestInterface
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
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
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
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
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
     * @param   \Magento\Framework\View\LayoutInterface $layout
     * @return  $this
     */
    public function setLayout(\Magento\Framework\View\LayoutInterface $layout)
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
     * @return \Magento\Framework\View\LayoutInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLayout()
    {
        if (!$this->_layout) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Layout must be initialized')
            );
        }
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
        if (!empty($this->_nameInLayout) && $this->_layout) {
            if ($name === $this->_nameInLayout) {
                return $this;
            }
            $this->_layout->renameElement($this->_nameInLayout, $name);
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
            return [];
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
     * @param   \Magento\Framework\View\Element\AbstractBlock|string $block
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
    public function addChild($alias, $block, $data = [])
    {
        $block = $this->getLayout()->createBlock(
            $block,
            $this->getNameInLayout() . '.' . $alias,
            ['data' => $data]
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
        $args = func_get_args();
        $child = $this->getChildBlock($alias);
        if ($child) {
            $alias = array_shift($args);
            $callback = array_shift($args);
            $result = (string)array_shift($args);
            if (!is_array($params)) {
                $params = $args;
            }

            // @codingStandardsIgnoreStart
            if ($result == call_user_func_array([&$child, $callback], $params)) {
                $this->unsetChild($alias);
            }
            // @codingStandardsIgnoreEnd
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
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
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
     * @param \Magento\Framework\View\Element\AbstractBlock|string $element
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
        if ($element instanceof \Magento\Framework\View\Element\AbstractBlock) {
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
     * @param \Magento\Framework\View\Element\AbstractBlock|string $element
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
        $this->_eventManager->dispatch('view_block_abstract_to_html_before', ['block' => $this]);
        if ($this->_scopeConfig->getValue(
            'advanced/modules_disable_output/' . $this->getModuleName(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return '';
        }

        $html = $this->_loadCache();
        $html = $this->_afterToHtml($html);

        /** @var \Magento\Framework\DataObject */
        $transportObject = new \Magento\Framework\DataObject(
            [
                'html' => $html,
            ]
        );
        $this->_eventManager->dispatch(
            'view_block_abstract_to_html_after',
            [
                'block' => $this,
                'transport' => $transportObject
            ]
        );
        $html = $transportObject->getHtml();

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
     * @param string|null $arg1
     * @param string|null $arg2
     * @param string|null $arg3
     * @param string|null $arg4
     * @param string|null $arg5
     * @return string
     */
    public function getUiId($arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null)
    {
        return ' data-ui-id="' . $this->escapeHtmlAttr($this->getJsId($arg1, $arg2, $arg3, $arg4, $arg5)) . '" ';
    }

    /**
     * Generate id for using in JavaScript UI
     *
     * Function takes an arbitrary amount of parameters
     *
     * @param string|null $arg1
     * @param string|null $arg2
     * @param string|null $arg3
     * @param string|null $arg4
     * @param string|null $arg5
     * @return string
     */
    public function getJsId($arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null)
    {
        $args = [];
        if ($arg1 !== null) {
            $args[] = $arg1;
        }
        if ($arg2 !== null) {
            $args[] = $arg2;
        }
        if ($arg3 !== null) {
            $args[] = $arg3;
        }
        if ($arg4 !== null) {
            $args[] = $arg4;
        }
        if ($arg5 !== null) {
            $args[] = $arg5;
        }
        $rawId = $this->_nameInLayout . '-' . implode('-', $args);
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($rawId)), '-');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->getRequest()->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
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
    protected function _getNotFoundUrl($route = '', $params = ['_direct' => 'core/index/notFound'])
    {
        return $this->getUrl($route, $params);
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|\DateTimeInterface $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     * @return string
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date ?? 'now');
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Retrieve formatting time
     *
     * @param   \DateTime|string|null $time
     * @param   int $format
     * @param   bool $showDate
     * @return  string
     */
    public function formatTime(
        $time = null,
        $format = \IntlDateFormatter::SHORT,
        $showDate = false
    ) {
        $time = $time instanceof \DateTimeInterface ? $time : new \DateTime($time ?? 'now');
        return $this->_localeDate->formatDateTime(
            $time,
            $showDate ? $format : \IntlDateFormatter::NONE,
            $format
        );
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
        if (!$className) {
            return '';
        }

        $namespace = substr(
            $className,
            0,
            (int)strpos($className, '\\' . 'Block' . '\\')
        );
        return str_replace('\\', '_', $namespace);
    }

    /**
     * Escape HTML entities
     *
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->_escaper->escapeHtml($data, $allowedTags);
    }

    /**
     * Escape string for the JavaScript context
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     */
    public function escapeJs($string)
    {
        return $this->_escaper->escapeJs($string);
    }

    /**
     * Escape a string for the HTML attribute context
     *
     * @param string $string
     * @param boolean $escapeSingleQuote
     * @return string
     * @since 101.0.0
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        return $this->_escaper->escapeHtmlAttr($string, $escapeSingleQuote);
    }

    /**
     * Escape string for the CSS context
     *
     * @param string $string
     * @return string
     * @since 101.0.0
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     */
    public function escapeCss($string)
    {
        return $this->_escaper->escapeCss($string);
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
        return $this->filterManager->stripTags(
            $data,
            ['allowableTags' => $allowableTags, 'escape' => $allowHtmlEntities]
        );
    }

    /**
     * Escape URL
     *
     * @param string $string
     * @return string
     * @deprecated 103.0.0 Use $escaper directly in templates and in blocks.
     */
    public function escapeUrl($string)
    {
        return $this->_escaper->escapeUrl((string)$string);
    }

    /**
     * Escape xss in urls
     *
     * @param string $data
     * @return string
     * @deprecated 101.0.0
     */
    public function escapeXssInUrl($data)
    {
        return $this->_escaper->escapeXssInUrl($data);
    }

    /**
     * Escape quotes inside html attributes
     *
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     * @deprecated 101.0.0
     */
    public function escapeQuote($data, $addSlashes = false)
    {
        return $this->_escaper->escapeQuote($data, $addSlashes);
    }

    /**
     * Escape single quotes/apostrophes ('), or other specified $quote character in javascript
     *
     * @param string|string[]|array $data
     * @param string $quote
     *
     * @return string|array
     * @deprecated 101.0.0
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
        return [$this->getNameInLayout()];
    }

    /**
     * Get Key for caching block content
     *
     * @return string
     */
    public function getCacheKey()
    {
        if ($this->hasData('cache_key')) {
            return static::CACHE_KEY_PREFIX . $this->getData('cache_key');
        }

        /**
         * don't prevent recalculation by saving generated cache key
         * because of ability to render single block instance with different data
         */
        $key = $this->getCacheKeyInfo();

        $key = array_values($key);  // ignore array keys

        $key = implode('|', $key);
        $key = sha1($key); // use hashing to hide potentially private data
        return static::CACHE_KEY_PREFIX . $key;
    }

    /**
     * Get tags array for saving cache
     *
     * @return array
     */
    protected function getCacheTags()
    {
        if (!$this->hasData('cache_tags')) {
            $tags = [];
        } else {
            $tags = $this->getData('cache_tags');
        }
        $tags[] = self::CACHE_GROUP;

        if ($this instanceof IdentityInterface) {
            $tags = array_merge($tags, $this->getIdentities());
        }
        return $tags;
    }

    /**
     * Get block cache life time
     *
     * @return int|bool|null
     */
    protected function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return null;
        }

        $cacheLifetime = $this->getData('cache_lifetime');
        if (false === $cacheLifetime || null === $cacheLifetime) {
            return null;
        }

        return (int)$cacheLifetime;
    }

    /**
     * Load block html from cache storage
     *
     * @return string
     */
    protected function _loadCache()
    {
        $collectAction = function () {
            if ($this->hasData('translate_inline')) {
                $this->inlineTranslation->suspend($this->getData('translate_inline'));
            }

            $this->_beforeToHtml();
            return $this->_toHtml();
        };

        if ($this->getCacheLifetime() === null || !$this->_cacheState->isEnabled(self::CACHE_GROUP)) {
            $html = $collectAction();
            if ($this->hasData('translate_inline')) {
                $this->inlineTranslation->resume();
            }
            return $html;
        }
        $loadAction = function () {
            return $this->_cache->load($this->getCacheKey());
        };

        $saveAction = function ($data) {
            $this->_saveCache($data);
            if ($this->hasData('translate_inline')) {
                $this->inlineTranslation->resume();
            }
        };

        return (string)$this->lockQuery->lockedLoadData(
            $this->getCacheKey(),
            $loadAction,
            $collectAction,
            $saveAction
        );
    }

    /**
     * Save block content to cache storage
     *
     * @param string $data
     * @return $this
     */
    protected function _saveCache($data)
    {
        if (!$this->getCacheLifetime() || !$this->_cacheState->isEnabled(self::CACHE_GROUP)) {
            return false;
        }
        $cacheKey = $this->getCacheKey();

        $this->_cache->save($data, $cacheKey, array_unique($this->getCacheTags()), $this->getCacheLifetime());
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
        if ($cacheKey === null) {
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
     *
     * Returns true if scope is private, false otherwise
     *
     * @return bool
     * @deprecated
     * @since 103.0.1
     */
    public function isScopePrivate()
    {
        return $this->_isScopePrivate;
    }
}
