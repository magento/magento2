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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Asset;

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
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * Enabled
     *
     * @var array
     */
    protected $enabled = array();

    /**
     * Minfiers
     *
     * @var \Magento\Code\Minifier[]
     */
    protected $minifiers = array();

    /**
     * Applicaiton State
     *
     * @var \Magento\App\State
     */
    protected $appState;

    /**
     * Filesystem instance
     *
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\State $appState
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(
        ConfigInterface $config,
        \Magento\ObjectManager $objectManager,
        \Magento\App\State $appState,
        \Magento\App\Filesystem $filesystem
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->appState = $appState;
        $this->_filesystem = $filesystem;
    }

    /**
     * Get filtered assets
     * Assets applicable for minification are wrapped with the minified asset
     *
     * @param array|\Iterator $assets
     * @return array
     */
    public function getAssets($assets)
    {
        $resultAssets = array();
        /** @var $asset AssetInterface */
        foreach ($assets as $asset) {
            $contentType = $asset->getContentType();
            if ($this->isEnabled($contentType)) {
                $asset = $this->objectManager
                    ->create('Magento\View\Asset\Minified', array(
                        'asset' => $asset,
                        'minifier' => $this->getMinifier($contentType)
                    ));
            }
            $resultAssets[] = $asset;
        }
        return $resultAssets;
    }

    /**
     * Get minifier object configured with specified content type
     *
     * @param string $contentType
     * @return \Magento\Code\Minifier
     */
    protected function getMinifier($contentType)
    {
        if (!isset($this->minifiers[$contentType])) {
            $adapter = $this->getAdapter($contentType);
            $strategyParams = array(
                'adapter' => $adapter,
            );
            switch ($this->appState->getMode()) {
                case \Magento\App\State::MODE_PRODUCTION:
                    $strategy = $this->objectManager->create('Magento\Code\Minifier\Strategy\Lite', $strategyParams);
                    break;
                default:
                    $strategy = $this->objectManager
                        ->create('Magento\Code\Minifier\Strategy\Generate', $strategyParams);
            }
            $baseDir = $this->_filesystem
                ->getDirectoryRead(\Magento\App\Filesystem::PUB_VIEW_CACHE_DIR)
                ->getAbsolutePath('minify');

            $this->minifiers[$contentType] = $this->objectManager->create('Magento\Code\Minifier',
                array(
                    'strategy' => $strategy,
                    'directoryName' => $baseDir
                )
            );
        }
        return $this->minifiers[$contentType];
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
     * @return mixed
     * @throws \Magento\Exception
     */
    protected function getAdapter($contentType)
    {
        $adapterClass = $this->config->getAssetMinificationAdapter($contentType);
        if (!$adapterClass) {
            throw new \Magento\Exception(
                "Minification adapter is not specified for '$contentType' content type"
            );
        }

        $adapter = $this->objectManager->create($adapterClass);
        return $adapter;
    }
}
