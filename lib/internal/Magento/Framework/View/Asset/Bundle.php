<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\View\Asset\File\FallbackContext;

/**
 * Bundle model
 * @deprecated 101.0.0
 * @see \Magento\Deploy\Package\Bundle
 */
class Bundle
{
    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var array
     */
    protected $assetsContent = [];

    /**
     * @var \Magento\Framework\View\Asset\Bundle\ConfigInterface
     */
    protected $bundleConfig;

    /**
     * @var array
     */
    protected $bundleNames = [
        Manager::ASSET_TYPE_JS => 'jsbuild',
        Manager::ASSET_TYPE_HTML => 'text'
    ];

    /**
     * @var array
     */
    protected $content = [];

    /**
     * @var Minification
     */
    protected $minification;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     * @param Bundle\ConfigInterface $bundleConfig
     * @param Minification $minification
     */
    public function __construct(
        Filesystem $filesystem,
        Bundle\ConfigInterface $bundleConfig,
        Minification $minification
    ) {
        $this->filesystem = $filesystem;
        $this->bundleConfig = $bundleConfig;
        $this->minification = $minification;
    }

    /**
     * Init asset and add into array
     *
     * @param LocalInterface $asset
     * @return void
     */
    public function addAsset(LocalInterface $asset)
    {
        $this->init($asset);
        $this->add($asset);
    }

    /**
     * Add asset into array
     *
     * @param LocalInterface $asset
     * @return void
     */
    protected function add(LocalInterface $asset)
    {
        $partIndex = $this->getPartIndex($asset);
        $parts = &$this->assets[$this->getContextCode($asset)][$asset->getContentType()];
        if (!isset($parts[$partIndex])) {
            $parts[$partIndex]['assets'] = [];
        }
        $parts[$partIndex]['assets'][$this->getAssetKey($asset)] = $asset;
    }

    /**
     * Initialization
     *
     * @param LocalInterface $asset
     * @return void
     */
    protected function init(LocalInterface $asset)
    {
        $contextCode = $this->getContextCode($asset);
        $type = $asset->getContentType();

        if (!isset($this->assets[$contextCode][$type])) {
            $this->assets[$contextCode][$type] = [];
        }
    }

    /**
     * Returns the asset code based on context
     *
     * @param LocalInterface $asset
     * @return string
     */
    protected function getContextCode(LocalInterface $asset)
    {
        /** @var FallbackContext $context */
        $context = $asset->getContext();
        return $context->getAreaCode() . ':' . $context->getThemePath() . ':' . $context->getLocale();
    }

    /**
     * Returns a part index for the asset
     *
     * @param LocalInterface $asset
     * @return int
     */
    protected function getPartIndex(LocalInterface $asset)
    {
        $parts = $this->assets[$this->getContextCode($asset)][$asset->getContentType()];

        $maxPartSize = $this->getMaxPartSize($asset);
        $minSpace = $maxPartSize;
        $minIndex = -1;
        if ($maxPartSize && count($parts)) {
            foreach ($parts as $partIndex => $part) {
                $space = $maxPartSize - $this->getSizePartWithNewAsset($asset, $part['assets']);
                if ($space >= 0 && $space < $minSpace) {
                    $minSpace = $space;
                    $minIndex = $partIndex;
                }
            }
        }

        return ($maxPartSize != 0) ? ($minIndex >= 0) ? $minIndex : count($parts) : 0;
    }

    /**
     * Returns size of the part
     *
     * @param LocalInterface $asset
     *
     * @return int
     */
    protected function getMaxPartSize(LocalInterface $asset)
    {
        return $this->bundleConfig->getPartSize($asset->getContext());
    }

    /**
     * Get part size after adding new asset
     *
     * @param LocalInterface $asset
     * @param LocalInterface[] $assets
     * @return float
     */
    protected function getSizePartWithNewAsset(LocalInterface $asset, $assets = [])
    {
        $assets[$this->getAssetKey($asset)] = $asset;
        return mb_strlen($this->getPartContent($assets), 'utf-8') / 1024;
    }

    /**
     * Build asset key
     *
     * @param LocalInterface $asset
     * @return string
     */
    protected function getAssetKey(LocalInterface $asset)
    {
        $result = (($asset->getModule() == '') ? '' : $asset->getModule() . '/') . $asset->getFilePath();
        $result = $this->minification->addMinifiedSign($result);
        return $result;
    }

    /**
     * Prepare bundle for executing in js
     *
     * @param LocalInterface[] $assets
     * @return array
     */
    protected function getPartContent($assets)
    {
        $contents = [];
        foreach ($assets as $key => $asset) {
            $contents[$key] = $this->getAssetContent($asset);
        }

        $partType = reset($assets)->getContentType();
        $content = json_encode($contents, JSON_UNESCAPED_SLASHES);
        $content = "require.config({\n" .
            "    config: {\n" .
            "        '" . $this->bundleNames[$partType] . "':" . $content . "\n" .
            "    }\n" .
            "});\n";

        return $content;
    }

    /**
     * Get content of asset
     *
     * @param LocalInterface $asset
     * @return string
     */
    protected function getAssetContent(LocalInterface $asset)
    {
        $assetContextCode = $this->getContextCode($asset);
        $assetContentType = $asset->getContentType();
        $assetKey = $this->getAssetKey($asset);
        if (!isset($this->assetsContent[$assetContextCode][$assetContentType][$assetKey])) {
            $content = $asset->getContent();
            if (mb_detect_encoding($content) !== "UTF-8") {
                $content = $content !== null ? mb_convert_encoding($content, "UTF-8") : '';
            }
            $this->assetsContent[$assetContextCode][$assetContentType][$assetKey] = $content;
        }

        return $this->assetsContent[$assetContextCode][$assetContentType][$assetKey];
    }

    /**
     * Returns require.config init
     *
     * @return string
     */
    protected function getInitJs()
    {
        return "require.config({\n" .
                "    bundles: {\n" .
                "        'mage/requirejs/static': [\n" .
                "            'jsbuild',\n" .
                "            'buildTools',\n" .
                "            'text',\n" .
                "            'statistician'\n" .
                "        ]\n" .
                "    },\n" .
                "    deps: [\n" .
                "        'jsbuild'\n" .
                "    ]\n" .
                "});\n";
    }

    /**
     * Stores bundle types and flush data
     *
     * @return void
     */
    public function flush()
    {
        foreach ($this->assets as $types) {
            $this->save($types);
        }
        $this->assets = [];
        $this->content = [];
        $this->assetsContent = [];
    }

    /**
     * Save bundle
     *
     * @param array $types
     *
     * @return void
     */
    protected function save($types)
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        $bundlePath = '';
        foreach ($types as $parts) {
            /** @var FallbackContext $context */
            $assetsParts = reset($parts);
            $context = reset($assetsParts['assets'])->getContext();
            $bundlePath = empty($bundlePath) ? $context->getPath() . Manager::BUNDLE_PATH : $bundlePath;
            $dir->delete($context->getPath() . DIRECTORY_SEPARATOR . Manager::BUNDLE_JS_DIR);
            $this->fillContent($parts, $context);
        }

        $this->content[max(0, count($this->content) - 1)] .= $this->getInitJs();

        foreach ($this->content as $partIndex => $content) {
            $dir->writeFile($this->minification->addMinifiedSign($bundlePath . $partIndex . '.js'), $content);
        }
    }

    /**
     * Set bundle content
     *
     * @param array $parts
     * @param FallbackContext $context
     *
     * @return void
     */
    protected function fillContent($parts, $context)
    {
        $index = count($this->content) > 0 ? count($this->content) - 1 : 0;
        foreach ($parts as $part) {
            if (!isset($this->content[$index])) {
                $this->content[$index] = '';
            } elseif ($this->bundleConfig->isSplit($context)) {
                ++$index;
                $this->content[$index] = '';
            }
            $this->content[$index] .= $this->getPartContent($part['assets']);
        }
    }
}
