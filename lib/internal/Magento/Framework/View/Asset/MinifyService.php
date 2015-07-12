<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Service model responsible for configuration of minified asset
 */
class MinifyService
{
    /**
     * Config
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * ObjectManager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Enabled
     *
     * @var array
     */
    protected $enabled = [];

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface[]
     */
    protected $adapters = [];

    /**
     * @var string
     */
    protected $appMode;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $appMode
     */
    public function __construct(
        ConfigInterface $config,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $appMode = \Magento\Framework\App\State::MODE_DEFAULT
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->appMode = $appMode;
    }

    /**
     * Get filtered assets
     * Assets applicable for minification are wrapped with the minified asset
     *
     * @param array|\Iterator $assets
     * @param bool $isDirectRequest
     * @return Minified\AbstractAsset[]
     */
    public function getAssets($assets, $isDirectRequest = false)
    {
        $resultAssets = [];
        $strategy = $this->appMode == \Magento\Framework\App\State::MODE_PRODUCTION
            ? Minified\AbstractAsset::FILE_EXISTS : Minified\AbstractAsset::MTIME;
        /** @var $asset AssetInterface */
        foreach ($assets as $asset) {
            if ($this->isEnabled($asset->getContentType())) {
                $asset = $this->getAssetDecorated($asset, $strategy, $isDirectRequest);
            }
            $resultAssets[] = $asset;
        }
        return $resultAssets;
    }

    /**
     * Check if minification is enabled for specified content type
     *
     * @param string $contentType
     * @return bool
     */
    protected function isEnabled($contentType)
    {
        if (!isset($this->enabled[$contentType])) {
            $this->enabled[$contentType] = $this->config->isAssetMinification($contentType);
        }
        return $this->enabled[$contentType];
    }

    /**
     * Get minification adapter by specified content type
     *
     * @param string $contentType
     * @return \Magento\Framework\Code\Minifier\AdapterInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAdapter($contentType)
    {
        if (!isset($this->adapters[$contentType])) {
            $adapterClass = $this->config->getAssetMinificationAdapter($contentType);
            if (!$adapterClass) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        "Minification adapter is not specified for '%1' content type",
                        [$contentType]
                    )
                );
            }
            $adapter = $this->objectManager->get($adapterClass);
            if (!($adapter instanceof \Magento\Framework\Code\Minifier\AdapterInterface)) {
                $type = get_class($adapter);
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        "Invalid adapter: '%1'. Expected: \\Magento\\Framework\\Code\\Minifier\\AdapterInterface",
                        [$type]
                    )
                );
            }
            $this->adapters[$contentType] = $adapter;
        }
        return $this->adapters[$contentType];
    }

    /**
     * Returns asset decorated by corresponding minifier
     *
     * @param AssetInterface $asset
     * @param string $strategy
     * @param bool $isDirectRequest
     * @return AssetInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAssetDecorated(AssetInterface $asset, $strategy, $isDirectRequest)
    {
        return
            $this->objectManager->create(
                $this->getDecoratorClass($asset, $isDirectRequest),
                [
                    'asset' => $asset,
                    'strategy' => $strategy,
                    'adapter' => $this->getAdapter($asset->getContentType())
                ]
            );
    }

    /**
     * Returns minifier decorator class name for given asset
     *
     * @param AssetInterface $asset
     * @param bool $isDirectRequest
     * @return string
     */
    protected function getDecoratorClass(AssetInterface $asset, $isDirectRequest)
    {
        if ($isDirectRequest || $asset->getContentType() == 'css') {
            $result = 'Magento\Framework\View\Asset\Minified\ImmutablePathAsset';
        } else {
            $result = 'Magento\Framework\View\Asset\Minified\MutablePathAsset';
        }
        return $result;
    }
}
