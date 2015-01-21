<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Layout;

use Magento\Core\Model\Layout\Update\Validator;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Layout merge model
 */
class Merge implements \Magento\Framework\View\Layout\ProcessorInterface
{
    /**
     * Layout abstraction based on designer prerogative.
     */
    const DESIGN_ABSTRACTION_CUSTOM = 'custom';

    /**
     * Layout generalization guaranteed to load into View
     */
    const DESIGN_ABSTRACTION_PAGE_LAYOUT = 'page_layout';

    /**
     * XPath of handles originally declared in layout updates
     */
    const XPATH_HANDLE_DECLARATION = '/layout[@design_abstraction]';

    /**
     * Name of an attribute that stands for data type of node values
     */
    const TYPE_ATTRIBUTE = 'xsi:type';

    /**
     * Cache id suffix for page layout
     */
    const PAGE_LAYOUT_CACHE_SUFFIX = 'page_layout';

    /**
     * @var \Magento\Core\Model\Theme
     */
    private $_theme;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $_store;

    /**
     * In-memory cache for loaded layout updates
     *
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $_layoutUpdatesCache;

    /**
     * Cumulative array of update XML strings
     *
     * @var array
     */
    protected $_updates = [];

    /**
     * Handles used in this update
     *
     * @var array
     */
    protected $_handles = [];

    /**
     * Page handle names sorted by from parent to child
     *
     * @var array
     */
    protected $_pageHandles = [];

    /**
     * Substitution values in structure array('from' => array(), 'to' => array())
     *
     * @var array|null
     */
    protected $_subst = null;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    private $_fileSource;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    private $pageLayoutFileSource;

    /**
     * @var \Magento\Core\Model\Resource\Layout\Update
     */
    private $_resource;

    /**
     * @var \Magento\Framework\App\State
     */
    private $_appState;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var \Magento\Core\Model\Layout\Update\Validator
     */
    protected $_layoutValidator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var string
     */
    protected $cacheSuffix;

    /**
     * Status for new added handle
     *
     * @var int
     */
    protected $handleAdded = 1;

    /**
     * Status for handle being processed
     *
     * @var int
     */
    protected $handleProcessing = 2;

    /**
     * Status for processed handle
     *
     * @var int
     */
    protected $handleProcessed = 3;

    /**
     * Init merge model
     *
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\File\CollectorInterface $fileSource
     * @param \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource
     * @param \Magento\Core\Model\Resource\Layout\Update $resource
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Core\Model\Layout\Update\Validator $validator
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Design\ThemeInterface $theme Non-injectable theme instance
     * @param string $cacheSuffix
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\File\CollectorInterface $fileSource,
        \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource,
        \Magento\Core\Model\Resource\Layout\Update $resource,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Core\Model\Layout\Update\Validator $validator,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Design\ThemeInterface $theme = null,
        $cacheSuffix = ''
    ) {
        $this->_theme = $theme ?: $design->getDesignTheme();
        $this->_store = $storeManager->getStore();
        $this->_fileSource = $fileSource;
        $this->pageLayoutFileSource = $pageLayoutFileSource;
        $this->_resource = $resource;
        $this->_appState = $appState;
        $this->_cache = $cache;
        $this->_layoutValidator = $validator;
        $this->_logger = $logger;
        $this->filesystem = $filesystem;
        $this->cacheSuffix = $cacheSuffix;
    }

    /**
     * Add XML update instruction
     *
     * @param string $update
     * @return $this
     */
    public function addUpdate($update)
    {
        $this->_updates[] = $update;
        return $this;
    }

    /**
     * Get all registered updates as array
     *
     * @return array
     */
    public function asArray()
    {
        return $this->_updates;
    }

    /**
     * Get all registered updates as string
     *
     * @return string
     */
    public function asString()
    {
        return implode('', $this->_updates);
    }

    /**
     * Add handle(s) to update
     *
     * @param array|string $handleName
     * @return $this
     */
    public function addHandle($handleName)
    {
        if (is_array($handleName)) {
            foreach ($handleName as $name) {
                $this->_handles[$name] = $this->handleAdded;
            }
        } else {
            $this->_handles[$handleName] = $this->handleAdded;
        }
        return $this;
    }

    /**
     * Remove handle from update
     *
     * @param string $handleName
     * @return $this
     */
    public function removeHandle($handleName)
    {
        unset($this->_handles[$handleName]);
        return $this;
    }

    /**
     * Get handle names array
     *
     * @return array
     */
    public function getHandles()
    {
        return array_keys($this->_handles);
    }

    /**
     * Add the first existing (declared in layout updates) page handle along with all parents to the update.
     * Return whether any page handles have been added or not.
     *
     * @param string[] $handlesToTry
     * @return bool
     */
    public function addPageHandles(array $handlesToTry)
    {
        $handlesAdded = false;
        foreach ($handlesToTry as $handleName) {
            if (!$this->pageHandleExists($handleName)) {
                continue;
            }
            $handles[] = $handleName;
            $this->_pageHandles = $handles;
            $this->addHandle($handles);
            $handlesAdded = true;
        }
        return $handlesAdded;
    }

    /**
     * Whether a page handle is declared in the system or not
     *
     * @param string $handleName
     * @return bool
     */
    public function pageHandleExists($handleName)
    {
        return (bool)$this->_getPageHandleNode($handleName);
    }

    /**
     * @return string|null
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * Check current handles if layout was defined on it
     *
     * @return bool
     */
    public function isLayoutDefined()
    {
        $fullLayoutXml = $this->getFileLayoutUpdatesXml();
        foreach ($this->getHandles() as $handle) {
            if ($fullLayoutXml->xpath("layout[@id='{$handle}']")) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get handle xml node by handle name
     *
     * @param string $handleName
     * @return \Magento\Framework\View\Layout\Element|null
     */
    protected function _getPageHandleNode($handleName)
    {
        /* quick validation for non-existing page types */
        if (!$handleName) {
            return null;
        }
        $handles = $this->getFileLayoutUpdatesXml()->xpath("handle[@id='{$handleName}']");
        if (empty($handles)) {
            return null;
        }
        $nodes = $this->getFileLayoutUpdatesXml()->xpath("/layouts/handle[@id=\"{$handleName}\"]");
        return $nodes ? reset($nodes) : null;
    }

    /**
     * Retrieve used page handle names sorted from parent to child
     *
     * @return array
     */
    public function getPageHandles()
    {
        return $this->_pageHandles;
    }

    /**
     * Retrieve all design abstractions that exist in the system.
     *
     * Result format:
     * array(
     *     'handle_name_1' => array(
     *         'name'     => 'handle_name_1',
     *         'label'    => 'Handle Name 1',
     *         'design_abstraction' => self::DESIGN_ABSTRACTION_PAGE_LAYOUT or self::DESIGN_ABSTRACTION_CUSTOM
     *     ),
     *     // ...
     * )
     *
     * @return array
     */
    public function getAllDesignAbstractions()
    {
        $result = [];

        $conditions = [
            '(@design_abstraction="' . self::DESIGN_ABSTRACTION_PAGE_LAYOUT .
            '" or @design_abstraction="' . self::DESIGN_ABSTRACTION_CUSTOM . '")',
        ];
        $xpath = '/layouts/*[' . implode(' or ', $conditions) . ']';
        $nodes = $this->getFileLayoutUpdatesXml()->xpath($xpath) ?: [];
        /** @var $node \Magento\Framework\View\Layout\Element */
        foreach ($nodes as $node) {
            $name = $node->getAttribute('id');
            $info = [
                'name' => $name,
                'label' => __((string)$node->getAttribute('label')),
                'design_abstraction' => $node->getAttribute('design_abstraction'),
            ];
            $result[$name] = $info;
        }
        return $result;
    }

    /**
     * Retrieve the type of a page handle
     *
     * @param string $handleName
     * @return string|null
     */
    public function getPageHandleType($handleName)
    {
        $node = $this->_getPageHandleNode($handleName);
        return $node ? $node->getAttribute('type') : null;
    }

    /**
     * Load layout updates by handles
     *
     * @param array|string $handles
     * @throws \Magento\Framework\Exception
     * @return $this
     */
    public function load($handles = [])
    {
        if (is_string($handles)) {
            $handles = [$handles];
        } elseif (!is_array($handles)) {
            throw new \Magento\Framework\Exception('Invalid layout update handle');
        }

        $this->addHandle($handles);

        $cacheId = $this->_getCacheId(md5(implode('|', $this->getHandles())));
        $cacheIdPageLayout = $cacheId . '_' . self::PAGE_LAYOUT_CACHE_SUFFIX;
        $result = $this->_loadCache($cacheId);
        if ($result) {
            $this->addUpdate($result);
            $this->pageLayout = $this->_loadCache($cacheIdPageLayout);
            return $this;
        }

        foreach ($this->getHandles() as $handle) {
            $this->_merge($handle);
        }

        $layout = $this->asString();
        $this->_validateMergedLayout($cacheId, $layout);
        $this->_saveCache($layout, $cacheId, $this->getHandles());
        $this->_saveCache((string)$this->pageLayout, $cacheIdPageLayout, $this->getHandles());
        return $this;
    }

    /**
     * Validate merged layout
     *
     * @param string $cacheId
     * @param string $layout
     * @return $this
     */
    protected function _validateMergedLayout($cacheId, $layout)
    {
        $layoutStr = '<handle id="handle">' . $layout . '</handle>';
        if ($this->_appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER) {
            if (!$this->_layoutValidator->isValid($layoutStr, Validator::LAYOUT_SCHEMA_MERGED, false)) {
                $messages = $this->_layoutValidator->getMessages();
                //Add first message to exception
                $message = reset($messages);
                $this->_logger->info('Cache file with merged layout: ' . $cacheId . ': ' . $message);
            }
        }
        return $this;
    }

    /**
     * Get layout updates as \Magento\Framework\View\Layout\Element object
     *
     * @return \SimpleXMLElement
     */
    public function asSimplexml()
    {
        $updates = trim($this->asString());
        $updates = '<?xml version="1.0"?>'
            . '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . $updates
            . '</layout>';
        return $this->_loadXmlString($updates);
    }

    /**
     * Return object representation of XML string
     *
     * @param string $xmlString
     * @return \SimpleXMLElement
     */
    protected function _loadXmlString($xmlString)
    {
        return simplexml_load_string($xmlString, 'Magento\Framework\View\Layout\Element');
    }

    /**
     * Merge layout update by handle
     *
     * @param string $handle
     * @return $this
     */
    protected function _merge($handle)
    {
        if (!isset($this->_handles[$handle]) || $this->_handles[$handle] == $this->handleAdded) {
            $this->_handles[$handle] = $this->handleProcessing;
            $this->_fetchPackageLayoutUpdates($handle);
            $this->_fetchDbLayoutUpdates($handle);
            $this->_handles[$handle] = $this->handleProcessed;
        } elseif ($this->_handles[$handle] == $this->handleProcessing
            && $this->_appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER
        ) {
            $this->_logger->info('Cyclic dependency in merged layout for handle: ' . $handle);
        }
        return $this;
    }

    /**
     * Add updates for the specified handle
     *
     * @param string $handle
     * @return bool
     */
    protected function _fetchPackageLayoutUpdates($handle)
    {
        $_profilerKey = 'layout_package_update:' . $handle;
        \Magento\Framework\Profiler::start($_profilerKey);
        $layout = $this->getFileLayoutUpdatesXml();
        foreach ($layout->xpath("*[self::handle or self::layout][@id='{$handle}']") as $updateXml) {
            $this->_fetchRecursiveUpdates($updateXml);
            $this->addUpdate($updateXml->innerXml());
        }
        \Magento\Framework\Profiler::stop($_profilerKey);

        return true;
    }

    /**
     * Fetch & add layout updates for the specified handle from the database
     *
     * @param string $handle
     * @return bool
     */
    protected function _fetchDbLayoutUpdates($handle)
    {
        $_profilerKey = 'layout_db_update: ' . $handle;
        \Magento\Framework\Profiler::start($_profilerKey);
        $updateStr = $this->_getDbUpdateString($handle);
        if (!$updateStr) {
            \Magento\Framework\Profiler::stop($_profilerKey);
            return false;
        }
        $updateStr = '<update_xml xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            $updateStr .
            '</update_xml>';
        $updateStr = $this->_substitutePlaceholders($updateStr);
        $updateXml = $this->_loadXmlString($updateStr);
        $this->_fetchRecursiveUpdates($updateXml);
        $this->addUpdate($updateXml->innerXml());

        \Magento\Framework\Profiler::stop($_profilerKey);
        return (bool)$updateStr;
    }

    /**
     * Substitute placeholders {{placeholder_name}} with their values in XML string
     *
     * @param string $xmlString
     * @return string
     */
    protected function _substitutePlaceholders($xmlString)
    {
        if ($this->_subst === null) {
            $placeholders = [
                'baseUrl' => $this->_store->getBaseUrl(),
                'baseSecureUrl' => $this->_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true),
            ];
            $this->_subst = [];
            foreach ($placeholders as $key => $value) {
                $this->_subst['from'][] = '{{' . $key . '}}';
                $this->_subst['to'][] = $value;
            }
        }
        return str_replace($this->_subst['from'], $this->_subst['to'], $xmlString);
    }

    /**
     * Get update string
     *
     * @param string $handle
     * @return string
     */
    protected function _getDbUpdateString($handle)
    {
        return $this->_resource->fetchUpdatesByHandle($handle, $this->_theme, $this->_store);
    }

    /**
     * Add handles declared as '<update handle="handle_name"/>' directives
     *
     * @param \SimpleXMLElement $updateXml
     * @return $this
     */
    protected function _fetchRecursiveUpdates($updateXml)
    {
        foreach ($updateXml->children() as $child) {
            if (strtolower($child->getName()) == 'update' && isset($child['handle'])) {
                $this->_merge((string)$child['handle']);
            }
        }
        if (isset($updateXml['layout'])) {
            $this->pageLayout = (string)$updateXml['layout'];
        }
        return $this;
    }

    /**
     * Retrieve already merged layout updates from files for specified area/theme/package/store
     *
     * @return \Magento\Framework\View\Layout\Element
     */
    public function getFileLayoutUpdatesXml()
    {
        if ($this->_layoutUpdatesCache) {
            return $this->_layoutUpdatesCache;
        }
        $cacheId = $this->_getCacheId($this->cacheSuffix);
        $result = $this->_loadCache($cacheId);
        if ($result) {
            $result = $this->_loadXmlString($result);
        } else {
            $result = $this->_loadFileLayoutUpdatesXml();
            $this->_saveCache($result->asXml(), $cacheId);
        }
        $this->_layoutUpdatesCache = $result;
        return $result;
    }

    /**
     * Retrieve cache identifier taking into account current area/package/theme/store
     *
     * @param string $suffix
     * @return string
     */
    protected function _getCacheId($suffix = '')
    {
        return "LAYOUT_{$this->_theme->getArea()}_STORE{$this->_store->getId()}_{$this->_theme->getId()}{$suffix}";
    }

    /**
     * Retrieve data from the cache, if the layout caching is allowed, or FALSE otherwise
     *
     * @param string $cacheId
     * @return string|bool
     */
    protected function _loadCache($cacheId)
    {
        return $this->_cache->load($cacheId);
    }

    /**
     * Save data to the cache, if the layout caching is allowed
     *
     * @param string $data
     * @param string $cacheId
     * @param array $cacheTags
     * @return void
     */
    protected function _saveCache($data, $cacheId, array $cacheTags = [])
    {
        $this->_cache->save($data, $cacheId, $cacheTags, null);
    }

    /**
     * Collect and merge layout updates from files
     *
     * @return \Magento\Framework\View\Layout\Element
     * @throws \Magento\Framework\Exception
     */
    protected function _loadFileLayoutUpdatesXml()
    {
        $layoutStr = '';
        $theme = $this->_getPhysicalTheme($this->_theme);
        $updateFiles = $this->_fileSource->getFiles($theme, '*.xml');
        $updateFiles = array_merge($updateFiles, $this->pageLayoutFileSource->getFiles($theme, '*.xml'));
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $useErrors = libxml_use_internal_errors(true);
        foreach ($updateFiles as $file) {
            $filename = $dir->getRelativePath($file->getFilename());
            $fileStr = $dir->readFile($filename);
            $fileStr = $this->_substitutePlaceholders($fileStr);
            /** @var $fileXml \Magento\Framework\View\Layout\Element */
            $fileXml = $this->_loadXmlString($fileStr);
            if (!$fileXml instanceof \Magento\Framework\View\Layout\Element) {
                $this->_logXmlErrors($file->getFilename(), libxml_get_errors());
                libxml_clear_errors();
                continue;
            }
            if (!$file->isBase() && $fileXml->xpath(self::XPATH_HANDLE_DECLARATION)) {
                throw new \Magento\Framework\Exception(
                    sprintf("Theme layout update file '%s' must not declare page types.", $file->getFileName())
                );
            }
            $handleName = basename($file->getFilename(), '.xml');
            $tagName = $fileXml->getName() === 'layout' ? 'layout' : 'handle';
            $handleAttributes = ' id="' . $handleName . '"' . $this->_renderXmlAttributes($fileXml);
            $handleStr = '<' . $tagName . $handleAttributes . '>' . $fileXml->innerXml() . '</' . $tagName . '>';
            $layoutStr .= $handleStr;
        }
        libxml_use_internal_errors($useErrors);
        $layoutStr = '<layouts xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $layoutStr . '</layouts>';
        $layoutXml = $this->_loadXmlString($layoutStr);
        return $layoutXml;
    }

    /**
     * Log xml errors to system log
     *
     * @param string $fileName
     * @param array $libXmlErrors
     * @return void
     */
    protected function _logXmlErrors($fileName, $libXmlErrors)
    {
        $errors = [];
        if (count($libXmlErrors)) {
            foreach ($libXmlErrors as $error) {
                $errors[] = "{$error->message} Line: {$error->line}";
            }

            $this->_logger->info(
                sprintf("Theme layout update file '%s' is not valid.\n%s", $fileName, implode("\n", $errors))
            );
        }
    }

    /**
     * Find the closest physical theme among ancestors and a theme itself
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Core\Model\Theme
     * @throws \Magento\Framework\Exception
     */
    protected function _getPhysicalTheme(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $result = $theme;
        while ($result->getId() && !$result->isPhysical()) {
            $result = $result->getParentTheme();
        }
        if (!$result) {
            throw new \Magento\Framework\Exception(
                "Unable to find a physical ancestor for a theme '{$theme->getThemeTitle()}'."
            );
        }
        return $result;
    }

    /**
     * Return attributes of XML node rendered as a string
     *
     * @param \SimpleXMLElement $node
     * @return string
     */
    protected function _renderXmlAttributes(\SimpleXMLElement $node)
    {
        $result = '';
        foreach ($node->attributes() as $attributeName => $attributeValue) {
            $result .= ' ' . $attributeName . '="' . $attributeValue . '"';
        }
        return $result;
    }

    /**
     * Retrieve containers from the update handles that have been already loaded
     *
     * Result format:
     * array(
     *     'container_name' => 'Container Label',
     *     // ...
     * )
     *
     * @return array
     */
    public function getContainers()
    {
        $result = [];
        $containerNodes = $this->asSimplexml()->xpath('//container');
        /** @var $oneContainerNode \Magento\Framework\View\Layout\Element */
        foreach ($containerNodes as $oneContainerNode) {
            $label = $oneContainerNode->getAttribute('label');
            if ($label) {
                $result[$oneContainerNode->getAttribute('name')] = __($label);
            }
        }
        return $result;
    }

    /**
     * Cleanup circular references
     *
     * Destructor should be called explicitly in order to work around the PHP bug
     * https://bugs.php.net/bug.php?id=62468
     */
    public function __destruct()
    {
        $this->_updates = [];
        $this->_layoutUpdatesCache = null;
    }

    /**
     * @inheritdoc
     */
    public function isCustomerDesignAbstraction(array $abstraction)
    {
        if (!isset($abstraction['design_abstraction'])) {
            return false;
        }
        return $abstraction['design_abstraction'] === self::DESIGN_ABSTRACTION_CUSTOM;
    }

    /**
     * @inheritdoc
     */
    public function isPageLayoutDesignAbstraction(array $abstraction)
    {
        if (!isset($abstraction['design_abstraction'])) {
            return false;
        }
        return $abstraction['design_abstraction'] === self::DESIGN_ABSTRACTION_PAGE_LAYOUT;
    }
}
