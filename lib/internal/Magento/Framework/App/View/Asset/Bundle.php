<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\App\View\Asset;

use Magento\Framework\View\Asset;

/**
 * Bundle model
 */
class Bundle
{
    /** @var string */
    protected $bundlePath;

    /** @var int */
    protected $bundleParts = 1;

    /** @var array */
    protected $assets = [];

    /** @var array */
    protected $bundle = [];

    /**
     * @var \Magento\Framework\View\Asset\MinifyServiceFactory
     */
    protected $minifyServiceFactory;

    /**
     * @param \Magento\Framework\View\Asset\MinifyServiceFactory
     */
    function __construct(
        \Magento\Framework\View\Asset\MinifyServiceFactory $minifyServiceFactory
    ) {
        $this->minifyServiceFactory = $minifyServiceFactory;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->bundlePath = $path;
    }

    /**
     * Get bundle save path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->bundlePath;
    }

    /**
     * Set minifyed asset to bundle list;
     *
     * @param Asset\LocalInterface $asset
     */
    public function addAsset(Asset\LocalInterface $asset)
    {
        $assetKey = $this->getAssetKey($asset);

        $minifyService = $this->minifyServiceFactory->create();
        $asset = $minifyService->getAssets([$asset])[0];

        $this->assets[$assetKey] = $asset;
    }

    /**
     * Build asset key
     *
     * @param Asset\LocalInterface $asset
     * @return string
     */
    protected function getAssetKey(Asset\LocalInterface $asset)
    {
        if ($asset->getModule() == '') {
            $key = $asset->getFilePath();
        } else {
            $key = $asset->getModule() . '/' . $asset->getFilePath();
        }

        return $key;
    }

    /**
     * Divided bundle on small parts
     */
    public function prepare()
    {
        $perBundlePart = ceil(count($this->assets) / $this->bundleParts);
        $this->bundle = array_chunk($this->assets, $perBundlePart, true);
        unset($this->assets);
    }

    /**
     * Fill bundle with real content
     */
    public function fill()
    {
        foreach ($this->assets as $path => $asset) {
            $this->assets[$path] = utf8_encode($asset->getContent());
        }
    }

    /**
     * Convert bundle content to json
     */
    public function toJson()
    {
        foreach ($this->bundle as &$part) {
            $part = json_encode($part, JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Prepare bundle part for executing in js
     */
    public function wrapp()
    {
        foreach ($this->bundle as &$part) {
               $part = "require.config({\n" .
                "    config: {\n" .
                "        'jsbuild':" . $part . "\n" .
                "    }\n" .
                "});\n";
        }

    }

    /**
     * Get bundle content
     *
     * @return array
     */
    public function getContent()
    {
        return $this->bundle;
    }
}
