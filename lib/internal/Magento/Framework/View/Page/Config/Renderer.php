<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Config\Metadata\MsApplicationTileImage;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Asset\MergeService;

/**
 * Page config Renderer model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Renderer implements RendererInterface
{
    /**
     * @var array
     */
    protected $assetTypeOrder = [
        'css',
        'ico',
        'js',
        'eot',
        'svg',
        'ttf',
        'woff',
        'woff2',
    ];

    /**
     * Possible fonts type
     *
     * @var array
     */
    private const FONTS_TYPE = [
        'eot',
        'svg',
        'ttf',
        'woff',
        'woff2',
    ];

    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * @var MergeService
     */
    protected $assetMergeService;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var StringUtils
     */
    protected $string;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MsApplicationTileImage
     */
    private $msApplicationTileImage;

    /**
     * @param Config $pageConfig
     * @param MergeService $assetMergeService
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param StringUtils $string
     * @param LoggerInterface $logger
     * @param MsApplicationTileImage|null $msApplicationTileImage
     */
    public function __construct(
        Config $pageConfig,
        MergeService $assetMergeService,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        StringUtils $string,
        LoggerInterface $logger,
        ?MsApplicationTileImage $msApplicationTileImage = null
    ) {
        $this->pageConfig = $pageConfig;
        $this->assetMergeService = $assetMergeService;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->string = $string;
        $this->logger = $logger;
        $this->msApplicationTileImage = $msApplicationTileImage ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(MsApplicationTileImage::class);
    }

    /**
     * Render element attributes
     *
     * @param string $elementType
     * @return string
     */
    public function renderElementAttributes($elementType)
    {
        $resultAttributes = [];
        foreach ($this->pageConfig->getElementAttributes($elementType) as $name => $value) {
            $resultAttributes[] = sprintf('%s="%s"', $name, $value);
        }
        return implode(' ', $resultAttributes);
    }

    /**
     * Render head content
     *
     * @return string
     */
    public function renderHeadContent()
    {
        $result = '';
        $result .= $this->renderMetadata();
        $result .= $this->renderTitle();
        $this->prepareFavicon();
        return $result;
    }

    /**
     * Render head assets
     *
     * @return string
     */
    public function renderHeadAssets()
    {
        $result = '';
        $result .= $this->renderAssets($this->getAvailableResultGroups());
        $result .= $this->pageConfig->getIncludes();
        return $result;
    }

    /**
     * Render title
     *
     * @return string
     */
    public function renderTitle()
    {
        return '<title>' . $this->escaper->escapeHtml($this->pageConfig->getTitle()->get()) . '</title>' . "\n";
    }

    /**
     * Render metadata
     *
     * @return string
     */
    public function renderMetadata()
    {
        $result = '';
        foreach ($this->pageConfig->getMetadata() as $name => $content) {
            $metadataTemplate = $this->getMetadataTemplate($name);
            if (!$metadataTemplate) {
                continue;
            }
            $content = $this->processMetadataContent($name, $content);
            if ($content) {
                $result .= str_replace(['%name', '%content'], [$name, $content], $metadataTemplate);
            }
        }
        return $result;
    }

    /**
     * Process metadata content
     *
     * @param string $name
     * @param string $content
     * @return mixed
     */
    protected function processMetadataContent($name, $content)
    {
        $method = 'get' . $this->string->upperCaseWords($name, '_', '');
        if ($name === 'title') {
            if (!$content) {
                $content = $this->escaper->escapeHtml($this->pageConfig->$method()->get());
            }
            return $content;
        }
        if (method_exists($this->pageConfig, $method)) {
            $content = $this->pageConfig->$method();
        }
        if ($content && $name === $this->msApplicationTileImage::META_NAME) {
            $content = $this->msApplicationTileImage->getUrl($content);
        }

        return $content;
    }

    /**
     * Returns metadata template
     *
     * @param string $name
     * @return bool|string
     */
    protected function getMetadataTemplate($name)
    {
        if (strpos($name, 'og:') === 0) {
            return '<meta property="' . $name . '" content="%content"/>' . "\n";
        }

        switch ($name) {
            case Config::META_CHARSET:
                return '<meta charset="%content"/>' . "\n";

            case Config::META_CONTENT_TYPE:
                return '<meta http-equiv="Content-Type" content="%content"/>' . "\n";

            case Config::META_X_UI_COMPATIBLE:
                return '<meta http-equiv="X-UA-Compatible" content="%content"/>' . "\n";

            case Config::META_MEDIA_TYPE:
                return false;

            default:
                return '<meta name="%name" content="%content"/>' . "\n";
        }
    }

    /**
     * Favicon preparation
     *
     * @return void
     */
    public function prepareFavicon()
    {
        if ($this->pageConfig->getFaviconFile()) {
            $this->pageConfig->addRemotePageAsset(
                $this->pageConfig->getFaviconFile(),
                Generator\Head::VIRTUAL_CONTENT_TYPE_LINK,
                ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                'icon'
            );
            $this->pageConfig->addRemotePageAsset(
                $this->pageConfig->getFaviconFile(),
                Generator\Head::VIRTUAL_CONTENT_TYPE_LINK,
                ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
                'shortcut-icon'
            );
        } else {
            $this->pageConfig->addPageAsset(
                $this->pageConfig->getDefaultFavicon(),
                ['attributes' => ['rel' => 'icon', 'type' => 'image/x-icon']],
                'icon'
            );
            $this->pageConfig->addPageAsset(
                $this->pageConfig->getDefaultFavicon(),
                ['attributes' => ['rel' => 'shortcut icon', 'type' => 'image/x-icon']],
                'shortcut-icon'
            );
        }
    }

    /**
     * Returns rendered HTML for all Assets (CSS before)
     *
     * @param array $resultGroups
     *
     * @return string
     */
    public function renderAssets($resultGroups = [])
    {
        /** @var $group \Magento\Framework\View\Asset\PropertyGroup */
        foreach ($this->pageConfig->getAssetCollection()->getGroups() as $group) {
            $type = $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE);
            if (!isset($resultGroups[$type])) {
                $resultGroups[$type] = '';
            }
            $resultGroups[$type] .= $this->renderAssetGroup($group);
        }
        return implode('', $resultGroups);
    }

    /**
     * Returns rendered HTML for an Asset Group
     *
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string
     */
    protected function renderAssetGroup(\Magento\Framework\View\Asset\PropertyGroup $group)
    {
        $groupHtml = $this->renderAssetHtml($group);
        $groupHtml = $this->processIeCondition($groupHtml, $group);
        return $groupHtml;
    }

    /**
     * Process assets merge
     *
     * @param array $groupAssets
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return array
     */
    protected function processMerge($groupAssets, $group)
    {
        if ($group->getProperty(GroupedCollection::PROPERTY_CAN_MERGE) && count($groupAssets) > 1) {
            $groupAssets = $this->assetMergeService->getMergedAssets(
                $groupAssets,
                $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE)
            );
        }
        return $groupAssets;
    }

    /**
     * Returns group attributes
     *
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string|null
     */
    protected function getGroupAttributes($group)
    {
        $attributes = $group->getProperty('attributes');
        if (!empty($attributes)) {
            if (is_array($attributes)) {
                $attributesString = '';
                foreach ($attributes as $name => $value) {
                    $attributesString .= ' ' . $name . '="' . $this->escaper->escapeHtml($value) . '"';
                }
                $attributes = $attributesString;
            } else {
                $attributes = ' ' . $attributes;
            }
        }
        return $attributes;
    }

    /**
     * Add default attributes
     *
     * @param string $contentType
     * @param string $attributes
     * @return string
     */
    protected function addDefaultAttributes($contentType, $attributes)
    {
        if ($contentType === 'js') {
            return ' type="text/javascript" ' . $attributes;
        }

        if ($contentType === 'css') {
            return ' rel="stylesheet" type="text/css" ' . ($attributes ?: ' media="all"');
        }

        if ($this->canTypeBeFont($contentType)) {
            return 'rel="preload" as="font" crossorigin="anonymous"';
        }

        return $attributes;
    }

    /**
     * Returns assets template
     *
     * @param string $contentType
     * @param string|null $attributes
     * @return string
     */
    protected function getAssetTemplate($contentType, $attributes)
    {
        switch ($contentType) {
            case 'js':
                $groupTemplate = preg_replace('/\s+/', ' ', '<script ' . $attributes . ' src="%s"></script>') . "\n";
                break;

            case 'css':
            default:
                $groupTemplate = preg_replace('/\s+/', ' ', '<link ' . $attributes . ' href="%s" />') . "\n";
                break;
        }
        return $groupTemplate;
    }

    /**
     * Process IE condition
     *
     * @param string $groupHtml
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string
     */
    protected function processIeCondition($groupHtml, $group)
    {
        $ieCondition = $group->getProperty('ie_condition');
        if (!empty($ieCondition)) {
            $groupHtml = '<!--[if ' . $ieCondition . ']>' . "\n" . $groupHtml . '<![endif]-->' . "\n";
        }
        return $groupHtml;
    }

    /**
     * Render HTML tags referencing corresponding URLs
     *
     * @param \Magento\Framework\View\Asset\PropertyGroup $group
     * @return string
     */
    protected function renderAssetHtml(\Magento\Framework\View\Asset\PropertyGroup $group)
    {
        $assets = $this->processMerge($group->getAll(), $group);
        $attributes = $this->getGroupAttributes($group);

        $result = '';
        $template = '';
        try {
            /** @var $asset \Magento\Framework\View\Asset\AssetInterface */
            foreach ($assets as $asset) {
                $template = $this->getAssetTemplate(
                    $group->getProperty(GroupedCollection::PROPERTY_CONTENT_TYPE),
                    $this->addDefaultAttributes($this->getAssetContentType($asset), $attributes)
                );
                $result .= sprintf($template, $asset->getUrl());
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $result .= sprintf($template, $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']));
        }
        return $result;
    }

    /**
     * Check if file type can be font
     *
     * @param string $type
     * @return bool
     */
    private function canTypeBeFont(string $type): bool
    {
        return in_array($type, self::FONTS_TYPE, true);
    }

    /**
     * Get asset content type
     *
     * @param \Magento\Framework\View\Asset\AssetInterface $asset
     * @return string
     */
    protected function getAssetContentType(\Magento\Framework\View\Asset\AssetInterface $asset)
    {
        return $asset->getContentType();
    }

    /**
     * Returns available groups.
     *
     * @return array
     */
    public function getAvailableResultGroups()
    {
        return array_fill_keys($this->assetTypeOrder, '');
    }
}
