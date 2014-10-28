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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Page;

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
     * Allowed group of types
     *
     * @var array
     */
    protected $allowedTypes = [
        self::ELEMENT_TYPE_BODY,
        self::ELEMENT_TYPE_HTML,
        self::ELEMENT_TYPE_HEAD
    ];

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $titleChunks;

    /**
     * @var string
     */
    protected $pureTitle;

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
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\Asset\GroupedCollection $pageAssets
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Page\FaviconInterface $favicon
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\Asset\GroupedCollection $pageAssets,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Page\FaviconInterface $favicon
    ) {
        $this->assetRepo = $assetRepo;
        $this->pageAssets = $pageAssets;
        $this->scopeConfig = $scopeConfig;
        $this->favicon = $favicon;
    }

    /**
     * Set page title
     *
     * @param string|array $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $this->scopeConfig->getValue(
            'design/head/title_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) . ' ' . $this->prepareTitle($title) . ' ' . $this->scopeConfig->getValue(
            'design/head/title_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $this;
    }

    /**
     * @param array|string $title
     * @return string
     */
    protected function prepareTitle($title)
    {
        $this->titleChunks = '';
        $this->pureTitle = '';

        if (is_array($title)) {
            $this->titleChunks = $title;
            return implode(' / ', $title);
        }
        $this->pureTitle = $title;
        return $this->pureTitle;
    }

    /**
     * Retrieve title element text (encoded)
     *
     * @return string
     */
    public function getTitle()
    {
        if (empty($this->title)) {
            $this->title = $this->getDefaultTitle();
        }
        return htmlspecialchars(html_entity_decode(trim($this->title), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Same as getTitle(), but return only first item from chunk for backend pages
     *
     * @return mixed
     */
    public function getShortTitle()
    {
        if (!empty($this->titleChunks)) {
            return reset($this->titleChunks);
        } else {
            return $this->pureTitle;
        }
    }

    /**
     * Retrieve default title text
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return $this->scopeConfig->getValue(
            'design/head/default_title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $name
     * @param string $content
     * @return void
     */
    public function setMetadata($name, $content)
    {
        $this->metadata[$name] = $content;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
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
        if (empty($this->metadata['content_type'])) {
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
            array('attributes' => 'rel="alternate" type="application/rss+xml" title="' . $title . '"')
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
     * @throws \Magento\Framework\Exception
     */
    public function setElementAttribute($elementType, $attribute, $value)
    {
        if (array_search($elementType, $this->allowedTypes) === false) {
            throw new \Magento\Framework\Exception($elementType . ' isn\'t allowed');
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
        return isset($this->elements[$elementType][$attribute]) ? $this->elements[$elementType][$attribute] : null;
    }

    /**
     * @param string $elementType
     * @return string[]
     */
    public function getElementAttributes($elementType)
    {
        return isset($this->elements[$elementType]) ? $this->elements[$elementType] : [];
    }

    /**
     * Set page layout
     *
     * @param string $handle
     * @return $this
     * @throws \UnexpectedValueException
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
        if (empty($this->includes)) {
            $this->includes = $this->scopeConfig->getValue(
                'design/head/includes',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->includes;
    }
}
