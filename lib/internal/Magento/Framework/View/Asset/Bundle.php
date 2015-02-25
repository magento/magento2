<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Bundle\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle
{
    /**
     * @var array
     */
    protected $assets = [];

    /** @var Config  */
    protected $bundleConfig;

    /**
     * @var array
     */
    protected $bundleNames = [
        Manager::ASSET_TYPE_JS => 'jsbuild',
        Manager::ASSET_TYPE_HTML => 'text'
    ];

    /**
     * @param Filesystem $filesystem
     * @param Bundle\ConfigInterface $bundleConfig
     */
    public function __construct(
        Filesystem $filesystem,
        Bundle\ConfigInterface $bundleConfig
    ) {
        $this->filesystem = $filesystem;
        $this->bundleConfig = $bundleConfig;
    }


    /**
     * @param LocalInterface $asset
     *
     * @return void
     */
    public function addAsset(LocalInterface $asset)
    {
        $contextCode = $asset->getContext()->getAreaCode()
            . ':' . $asset->getContext()->getThemePath()
            . ':' . $asset->getContext()->getLocaleCode();
        $type = $asset->getContentType();
        if (!isset($this->assets[$contextCode][$type])) {
            $this->assets[$contextCode][$type] = [];
        }
        $parts = $this->assets[$contextCode][$type];

        $maxSize = $this->bundleConfig->getPartSize($asset->getContext());
        $assetSize = mb_strlen(utf8_encode($asset->getContent()), 'utf-8') / 1024;
        $minSpace = $maxSize + 1;
        $minIndex = -1;
        if ($maxSize && count($parts)) {
            foreach ($parts as $partIndex => $part) {
                $space = $part['space'] - $assetSize;
                if ($space >= 0 && $space < $minSpace) {
                    $minSpace = $space;
                    $minIndex = $partIndex;
                }
            }
        }
        $index = null;
        if ($maxSize == 0) {
            $index = 0;
        } elseif ($minIndex >= 0) {
            $index = $minIndex;
        } else {
            $index = count($parts);
        }
        if (!isset($this->assets[$contextCode][$type][$index])) {
            $this->assets[$contextCode][$type][$index]['assets'] = [];
            $this->assets[$contextCode][$type][$index]['space'] = $maxSize;
        }
        $this->assets[$contextCode][$type][$index]['assets'][$this->getAssetKey($asset)] = $asset;
        $this->assets[$contextCode][$type][$index]['space'] -= $assetSize;
    }

      /**
     * Build asset key
     *
     * @param LocalInterface $asset
     * @return string
     */
    protected function getAssetKey(LocalInterface $asset)
    {
        return ($asset->getModule() == '') ? $asset->getFilePath() : $asset->getModule() . '/' . $asset->getFilePath();
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
            $contents[$key] = utf8_encode($asset->getContent());
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
     * @return void
     */
    public function flush()
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        foreach ($this->assets as $types) {
            $content = '';
            $bundlePath = '';
            $partIndex = 1;
            $isSplit = null;
            foreach ($types as $parts) {
                /** @var LocalInterface $firstAsset */
                $firstAsset = reset(reset($parts)['assets']);
                $amountParts = isset($types[Manager::ASSET_TYPE_JS]) ? count($types[Manager::ASSET_TYPE_JS]) : '';
                $amountParts += isset($types[Manager::ASSET_TYPE_HTML]) ? count($types[Manager::ASSET_TYPE_HTML]) : 0;
                if ($firstAsset) {
                    $bundlePath = $firstAsset->getContext()->getPath() . Manager::BUNDLE_PATH;
                    $isSplit = $this->bundleConfig->isSplit($firstAsset->getContext());
                    foreach ($parts as $part) {
                        if ($isSplit) {
                            $content = '';
                        }
                        $content .= $this->getPartContent($part['assets']);
                        if ($partIndex == $amountParts) {
                            $content = $content . $this->getInitJs();
                        }
                        if ($isSplit) {
                            $dir->writeFile($bundlePath . "$partIndex.js", $content);
                        }
                        $partIndex++;
                    }
                }
            }
            if ($bundlePath && !$isSplit) {
                $dir->writeFile($bundlePath . "1.js", $content);
            }
        }

        $this->assets = [];
    }
}
