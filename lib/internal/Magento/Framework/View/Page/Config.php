<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page;

use Magento\Framework\App;
use Magento\Framework\View;

/**
 * An API for page configuration
 *
 * Has methods for managing properties specific to web pages:
 * - title
 * - related documents, linked static assets in particular
 * - meta info
 * - root element properties
 * - etc...
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @api
 */
class Config
{
    /**#@+
     * Constants of available types
     */
    const ELEMENT_TYPE_BODY = 'body';
    const ELEMENT_TYPE_HTML = 'html';
    const ELEMENT_TYPE_HEAD = 'head';
    /**#@-*/

    /**
     * Constant body attribute class
     */
    const BODY_ATTRIBUTE_CLASS = 'class';

    /**
     * Constant html language attribute
     */
    const HTML_ATTRIBUTE_LANG = 'lang';

    /**
     * Allowed group of types
     *
     * @var array
     */
    protected $allowedTypes = [
        self::ELEMENT_TYPE_BODY,
        self::ELEMENT_TYPE_HTML,
        self::ELEMENT_TYPE_HEAD,
    ];

    /**
     * @var Title
     */
    protected $title;

    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\View\Asset\GroupedCollection
     */
    protected $pageAssets;

    /**
     * @var string[][]
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $pageLayout;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Page\FaviconInterface
     */
    protected $favicon;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\View\Layout\BuilderInterface
     */
    protected $builder;

    /**
     * @var array
     */
    protected $includes;

    /**
     * @var array
     */
    protected $metadata = [
        'charset' => null,
        'media_type' => null,
        'content_type' => null,
        'description' => null,
        'keywords' => null,
        'robots' => null,
    ];

    /**
     * @var \Magento\Framework\App\State
     */
    private $areaResolver;

    /**
     * @var bool
     */
    private $isIncludesAvailable;

    /**
     * This getter serves as a workaround to add this dependency to this class without breaking constructor structure.
     *
     * @return \Magento\Framework\App\State
     *
     * @deprecated 100.0.7
     */
    private function getAreaResolver()
    {
        if ($this->areaResolver === null) {
            $this->areaResolver = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\State::class);
        }
        return $this->areaResolver;
    }

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\Asset\GroupedCollection $pageAssets
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Page\FaviconInterface $favicon
     * @param Title $title
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param bool $isIncludesAvailable
     */
    public function __construct(
        View\Asset\Repository $assetRepo,
        View\Asset\GroupedCollection $pageAssets,
        App\Config\ScopeConfigInterface $scopeConfig,
        View\Page\FaviconInterface $favicon,
        Title $title,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $isIncludesAvailable = true
    ) {
        $this->assetRepo = $assetRepo;
        $this->pageAssets = $pageAssets;
        $this->scopeConfig = $scopeConfig;
        $this->favicon = $favicon;
        $this->title = $title;
        $this->localeResolver = $localeResolver;
        $this->isIncludesAvailable = $isIncludesAvailable;
        $this->setElementAttribute(
            self::ELEMENT_TYPE_HTML,
            self::HTML_ATTRIBUTE_LANG,
            strstr($this->localeResolver->getLocale(), '_', true)
        );
    }

    /**
     * @param View\Layout\BuilderInterface $builder
     * @return $this
     */
    public function setBuilder(View\Layout\BuilderInterface $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * Build page config from page configurations
     * @return void
     */
    protected function build()
    {
        if (!empty($this->builder)) {
            $this->builder->build();
        }
    }

    /**
     * TODO Will be eliminated in MAGETWO-28359
     * @return void
     */
    public function publicBuild()
    {
        $this->build();
    }

    /**
     * Retrieve title element text (encoded)
     *
     * @return Title
     */
    public function getTitle()
    {
        $this->build();
        return $this->title;
    }

    /**
     * @param string $name
     * @param string $content
     * @return void
     */
    public function setMetadata($name, $content)
    {
        $this->build();
        $this->metadata[$name] = htmlspecialchars($content);
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        $this->build();
        return $this->metadata;
    }

    /**
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType)
    {
        $this->setMetadata('content_type', $contentType);
    }

    /**
     * Retrieve Content Type
     *
     * @return string
     */
    public function getContentType()
    {
        $this->build();
        if (strtolower($this->metadata['content_type']) === 'auto') {
            $this->metadata['content_type'] = $this->getMediaType() . '; charset=' . $this->getCharset();
        }
        return $this->metadata['content_type'];
    }

    /**
     * @param string $mediaType
     * @return void
     */
    public function setMediaType($mediaType)
    {
        $this->setMetadata('media_type', $mediaType);
    }

    /**
     * Retrieve Media Type
     *
     * @return string
     */
    public function getMediaType()
    {
        $this->build();
        if (empty($this->metadata['media_type'])) {
            $this->metadata['media_type'] = $this->scopeConfig->getValue(
                'design/head/default_media_type',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->metadata['media_type'];
    }

    /**
     * @param string $charset
     * @return void
     */
    public function setCharset($charset)
    {
        $this->setMetadata('charset', $charset);
    }

    /**
     * Retrieve Charset
     *
     * @return string
     */
    public function getCharset()
    {
        $this->build();
        if (empty($this->metadata['charset'])) {
            $this->metadata['charset'] = $this->scopeConfig->getValue(
                'design/head/default_charset',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->metadata['charset'];
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->setMetadata('description', $description);
    }

    /**
     * Retrieve content for description tag
     *
     * @return string
     */
    public function getDescription()
    {
        $this->build();
        if (empty($this->metadata['description'])) {
            $this->metadata['description'] = $this->scopeConfig->getValue(
                'design/head/default_description',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->metadata['description'];
    }

    /**
     * @param string $keywords
     * @return void
     */
    public function setKeywords($keywords)
    {
        $this->setMetadata('keywords', $keywords);
    }

    /**
     * Retrieve content for keywords tag
     *
     * @return string
     */
    public function getKeywords()
    {
        $this->build();
        if (empty($this->metadata['keywords'])) {
            $this->metadata['keywords'] = $this->scopeConfig->getValue(
                'design/head/default_keywords',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->metadata['keywords'];
    }

    /**
     * @param string $robots
     * @return void
     */
    public function setRobots($robots)
    {
        $this->setMetadata('robots', $robots);
    }

    /**
     * Retrieve URL to robots file
     *
     * @return string
     */
    public function getRobots()
    {
        if ($this->getAreaResolver()->getAreaCode() !== 'frontend') {
            return 'NOINDEX,NOFOLLOW';
        }
        $this->build();
        if (empty($this->metadata['robots'])) {
            $this->metadata['robots'] = $this->scopeConfig->getValue(
                'design/search_engine_robots/default_robots',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->metadata['robots'];
    }

    /**
     * @return \Magento\Framework\View\Asset\GroupedCollection
     */
    public function getAssetCollection()
    {
        $this->build();
        return $this->pageAssets;
    }

    /**
     * @param string $file
     * @param array $properties
     * @param string|null $name
     * @return $this
     */
    public function addPageAsset($file, array $properties = [], $name = null)
    {
        $asset = $this->assetRepo->createAsset($file);
        $name = $name ?: $file;
        $this->pageAssets->add($name, $asset, $properties);

        return $this;
    }

    /**
     * Add remote page asset
     *
     * @param string $url
     * @param string $contentType
     * @param array $properties
     * @param string|null $name
     * @return $this
     */
    public function addRemotePageAsset($url, $contentType, array $properties = [], $name = null)
    {
        $remoteAsset = $this->assetRepo->createRemoteAsset($url, $contentType);
        $name = $name ?: $url;
        $this->pageAssets->add($name, $remoteAsset, $properties);

        return $this;
    }

    /**
     * Add RSS element
     *
     * @param string $title
     * @param string $href
     * @return $this
     */
    public function addRss($title, $href)
    {
        $remoteAsset = $this->assetRepo->createRemoteAsset((string)$href, 'unknown');
        $this->pageAssets->add(
            "link/{$href}",
            $remoteAsset,
            ['attributes' => 'rel="alternate" type="application/rss+xml" title="' . $title . '"']
        );

        return $this;
    }

    /**
     * Add CSS class to page body tag
     *
     * @param string $className
     * @return $this
     */
    public function addBodyClass($className)
    {
        $className = preg_replace('#[^a-z0-9]+#', '-', strtolower($className));
        $bodyClasses = $this->getElementAttribute(self::ELEMENT_TYPE_BODY, self::BODY_ATTRIBUTE_CLASS);
        $bodyClasses = $bodyClasses ? explode(' ', $bodyClasses) : [];
        $bodyClasses[] = $className;
        $bodyClasses = array_unique($bodyClasses);
        $this->setElementAttribute(
            self::ELEMENT_TYPE_BODY,
            self::BODY_ATTRIBUTE_CLASS,
            implode(' ', $bodyClasses)
        );
        return $this;
    }

    /**
     * Set additional element attribute
     *
     * @param string $elementType
     * @param string $attribute
     * @param mixed $value
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setElementAttribute($elementType, $attribute, $value)
    {
        $this->build();
        if (array_search($elementType, $this->allowedTypes) === false) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('%1 isn\'t allowed', [$elementType])
            );
        }
        $this->elements[$elementType][$attribute] = $value;
        return $this;
    }

    /**
     * Retrieve additional element attribute
     *
     * @param string $elementType
     * @param string $attribute
     * @return null
     */
    public function getElementAttribute($elementType, $attribute)
    {
        $this->build();
        return isset($this->elements[$elementType][$attribute]) ? $this->elements[$elementType][$attribute] : null;
    }

    /**
     * @param string $elementType
     * @return string[]
     */
    public function getElementAttributes($elementType)
    {
        $this->build();
        return isset($this->elements[$elementType]) ? $this->elements[$elementType] : [];
    }

    /**
     * Set page layout
     *
     * @param string $handle
     * @return $this
     */
    public function setPageLayout($handle)
    {
        $this->pageLayout = $handle;
        return $this;
    }

    /**
     * Return current page layout
     *
     * @return string
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * @return string
     */
    public function getFaviconFile()
    {
        return $this->favicon->getFaviconFile();
    }

    /**
     * @return string
     */
    public function getDefaultFavicon()
    {
        return $this->favicon->getDefaultFavicon();
    }

    /**
     * Get miscellaneous scripts/styles to be included in head before head closing tag
     *
     * @return string
     */
    public function getIncludes()
    {
        if (empty($this->includes) && $this->isIncludesAvailable) {
            $this->includes = $this->scopeConfig->getValue(
                'design/head/includes',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->includes;
    }
}
