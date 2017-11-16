<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\Layout;

use Magento\Framework\App\State;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\View\Model\Layout\Update\Validator;

/**
 * Layout merge model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    private $theme;

    /**
     * @var \Magento\Framework\Url\ScopeInterface
     */
    private $scope;

    /**
     * In-memory cache for loaded layout updates
     *
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $layoutUpdatesCache;

    /**
     * Cumulative array of update XML strings
     *
     * @var array
     */
    protected $updates = [];

    /**
     * Handles used in this update
     *
     * @var array
     */
    protected $handles = [];

    /**
     * Page handle names sorted by from parent to child
     *
     * @var array
     */
    protected $pageHandles = [];

    /**
     * Substitution values in structure array('from' => array(), 'to' => array())
     *
     * @var array|null
     */
    protected $subst = null;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    private $pageLayoutFileSource;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\Validator
     */
    protected $layoutValidator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var string
     */
    protected $cacheSuffix;

    /**
     * All processed handles used in this update
     *
     * @var array
     */
    protected $allHandles = [];

    /**
     * Status for handle being processed
     *
     * @var int
     */
    protected $handleProcessing = 1;

    /**
     * Status for processed handle
     *
     * @var int
     */
    protected $handleProcessed = 2;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * Init merge model
     *
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Url\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\View\File\CollectorInterface $fileSource
     * @param \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\View\Model\Layout\Update\Validator $validator
     * @param \Psr\Log\LoggerInterface $logger
     * @param ReadFactory $readFactory,
     * @param \Magento\Framework\View\Design\ThemeInterface $theme Non-injectable theme instance
     * @param string $cacheSuffix
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Url\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\View\File\CollectorInterface $fileSource,
        \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\View\Model\Layout\Update\Validator $validator,
        \Psr\Log\LoggerInterface $logger,
        ReadFactory $readFactory,
        \Magento\Framework\View\Design\ThemeInterface $theme = null,
        $cacheSuffix = ''
    ) {
        $this->theme = $theme ?: $design->getDesignTheme();
        $this->scope = $scopeResolver->getScope();
        $this->fileSource = $fileSource;
        $this->pageLayoutFileSource = $pageLayoutFileSource;
        $this->appState = $appState;
        $this->cache = $cache;
        $this->layoutValidator = $validator;
        $this->logger = $logger;
        $this->readFactory = $readFactory;
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
        if (!in_array($update, $this->updates)) {
            $this->updates[] = $update;
        }
        return $this;
    }

    /**
     * Get all registered updates as array
     *
     * @return array
     */
    public function asArray()
    {
        return $this->updates;
    }

    /**
     * Get all registered updates as string
     *
     * @return string
     */
    public function asString()
    {
        return implode('', $this->updates);
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
                $this->handles[$name] = 1;
            }
        } else {
            $this->handles[$handleName] = 1;
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
        unset($this->handles[$handleName]);
        return $this;
    }

    /**
     * Get handle names array
     *
     * @return array
     */
    public function getHandles()
    {
        return array_keys($this->handles);
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
            $this->pageHandles = $handles;
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
        return $this->pageHandles;
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
                'label' => (string)new \Magento\Framework\Phrase((string)$node->getAttribute('label')),
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function load($handles = [])
    {
        if (is_string($handles)) {
            $handles = [$handles];
        } elseif (!is_array($handles)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Invalid layout update handle')
            );
        }

        $this->addHandle($handles);

        $cacheId = $this->getCacheId();
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
     * @throws \Exception
     */
    protected function _validateMergedLayout($cacheId, $layout)
    {
        $layoutStr = '<handle id="handle">' . $layout . '</handle>';

        try {
            $this->layoutValidator->isValid($layoutStr, Validator::LAYOUT_SCHEMA_MERGED, false);
        } catch (\Exception $e) {
            $messages = $this->layoutValidator->getMessages();
            //Add first message to exception
            $message = reset($messages);
            $this->logger->info(
                'Cache file with merged layout: ' . $cacheId
                . ' and handles ' . implode(', ', (array)$this->getHandles()) . ': ' . $message
            );
            if ($this->appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER) {
                throw $e;
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
        return simplexml_load_string($xmlString, \Magento\Framework\View\Layout\Element::class);
    }

    /**
     * Merge layout update by handle
     *
     * @param string $handle
     * @return $this
     */
    protected function _merge($handle)
    {
        if (!isset($this->allHandles[$handle])) {
            $this->allHandles[$handle] = $this->handleProcessing;
            $this->_fetchPackageLayoutUpdates($handle);
            $this->_fetchDbLayoutUpdates($handle);
            $this->allHandles[$handle] = $this->handleProcessed;
        } elseif ($this->allHandles[$handle] == $this->handleProcessing
            && $this->appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER
        ) {
            $this->logger->info('Cyclic dependency in merged layout for handle: ' . $handle);
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
            $updateInnerXml = $updateXml->innerXml();
            $this->validateUpdate($handle, $updateInnerXml);
            $this->addUpdate($updateInnerXml);
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
        $updateStr = $this->getDbUpdateString($handle);
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
        $updateInnerXml = $updateXml->innerXml();
        $this->validateUpdate($handle, $updateInnerXml);
        $this->addUpdate($updateInnerXml);

        \Magento\Framework\Profiler::stop($_profilerKey);
        return (bool)$updateStr;
    }

    /**
     * Validate layout update content, throw exception on failure.
     *
     * This method is used as a hook for plugins.
     *
     * @param string $handle
     * @param string $updateXml
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function validateUpdate($handle, $updateXml)
    {
        return;
    }

    /**
     * Substitute placeholders {{placeholder_name}} with their values in XML string
     *
     * @param string $xmlString
     * @return string
     */
    protected function _substitutePlaceholders($xmlString)
    {
        if ($this->subst === null) {
            $placeholders = [
                'baseUrl' => $this->scope->getBaseUrl(),
                'baseSecureUrl' => $this->scope->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true),
            ];
            $this->subst = [];
            foreach ($placeholders as $key => $value) {
                $this->subst['from'][] = '{{' . $key . '}}';
                $this->subst['to'][] = $value;
            }
        }
        return str_replace($this->subst['from'], $this->subst['to'], $xmlString);
    }

    /**
     * Get update string
     *
     * @param string $handle
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDbUpdateString($handle)
    {
        return null;
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
        if ($this->layoutUpdatesCache) {
            return $this->layoutUpdatesCache;
        }
        $cacheId = $this->generateCacheId($this->cacheSuffix);
        $result = $this->_loadCache($cacheId);
        if ($result) {
            $result = $this->_loadXmlString($result);
        } else {
            $result = $this->_loadFileLayoutUpdatesXml();
            $this->_saveCache($result->asXml(), $cacheId);
        }
        $this->layoutUpdatesCache = $result;
        return $result;
    }

    /**
     * Generate cache identifier taking into account current area/package/theme/store
     *
     * @param string $suffix
     * @return string
     */
    protected function generateCacheId($suffix = '')
    {
        return "LAYOUT_{$this->theme->getArea()}_STORE{$this->scope->getId()}_{$this->theme->getId()}{$suffix}";
    }

    /**
     * Retrieve data from the cache, if the layout caching is allowed, or FALSE otherwise
     *
     * @param string $cacheId
     * @return string|bool
     */
    protected function _loadCache($cacheId)
    {
        return $this->cache->load($cacheId);
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
        $this->cache->save($data, $cacheId, $cacheTags, null);
    }

    /**
     * Collect and merge layout updates from files
     *
     * @return \Magento\Framework\View\Layout\Element
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _loadFileLayoutUpdatesXml()
    {
        $layoutStr = '';
        $theme = $this->_getPhysicalTheme($this->theme);
        $updateFiles = $this->fileSource->getFiles($theme, '*.xml');
        $updateFiles = array_merge($updateFiles, $this->pageLayoutFileSource->getFiles($theme, '*.xml'));
        $useErrors = libxml_use_internal_errors(true);
        foreach ($updateFiles as $file) {
            /** @var $fileReader \Magento\Framework\Filesystem\File\Read   */
            $fileReader = $this->readFactory->create($file->getFilename(), DriverPool::FILE);
            $fileStr = $fileReader->readAll($file->getName());
            $fileStr = $this->_substitutePlaceholders($fileStr);
            /** @var $fileXml \Magento\Framework\View\Layout\Element */
            $fileXml = $this->_loadXmlString($fileStr);
            if (!$fileXml instanceof \Magento\Framework\View\Layout\Element) {
                $xmlErrors = $this->getXmlErrors(libxml_get_errors());
                $this->_logXmlErrors($file->getFilename(), $xmlErrors);
                if ($this->appState->getMode() === State::MODE_DEVELOPER) {
                    throw new ValidationException(
                        new \Magento\Framework\Phrase(
                            "Theme layout update file '%1' is not valid.\n%2",
                            [
                                $file->getFilename(),
                                implode("\n", $xmlErrors)
                            ]
                        )
                    );
                }
                libxml_clear_errors();
                continue;
            }
            if (!$file->isBase() && $fileXml->xpath(self::XPATH_HANDLE_DECLARATION)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Theme layout update file \'%1\' must not declare page types.',
                        [$file->getFileName()]
                    )
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
     * @param array $xmlErrors
     * @return void
     */
    protected function _logXmlErrors($fileName, $xmlErrors)
    {
        $this->logger->info(
            sprintf("Theme layout update file '%s' is not valid.\n%s", $fileName, implode("\n", $xmlErrors))
        );
    }

    /**
     * Get formatted xml errors
     *
     * @param array $libXmlErrors
     * @return array
     */
    private function getXmlErrors($libXmlErrors)
    {
        $errors = [];
        if (count($libXmlErrors)) {
            foreach ($libXmlErrors as $error) {
                $errors[] = "{$error->message} Line: {$error->line}";
            }
        }
        return $errors;
    }

    /**
     * Find the closest physical theme among ancestors and a theme itself
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Theme\Model\Theme
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getPhysicalTheme(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $result = $theme;
        while ($result !== null && $result->getId() && !$result->isPhysical()) {
            $result = $result->getParentTheme();
        }
        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    'Unable to find a physical ancestor for a theme \'%1\'.',
                    [$theme->getThemeTitle()]
                )
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
                $result[$oneContainerNode->getAttribute('name')] = (string)new \Magento\Framework\Phrase($label);
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
        $this->updates = [];
        $this->layoutUpdatesCache = null;
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

    /**
     * Retrieve theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Retrieve current scope
     *
     * @return \Magento\Framework\Url\ScopeInterface
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Return cache ID based current area/package/theme/store and handles
     *
     * @return string
     */
    public function getCacheId()
    {
        return $this->generateCacheId(md5(implode('|', $this->getHandles())));
    }
}
