<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Block\Adminhtml\Html\Head;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\ConfigInterface as ViewAssetConfigInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context as ViewElementContext;
use Magento\Framework\View\Page\Config as ViewPageConfig;
use Magento\RequireJs\Model\FileManager as RequireJsFileManager;

/**
 * Block responsible for including RequireJs config on backend pages
 *
 * @api
 * @since 100.0.2
 */
class Config extends AbstractBlock
{
    /**
     * @var RequireJsConfig
     */
    protected $config;

    /**
     * @var RequireJsFileManager
     */
    protected $fileManager;

    /**
     * @var ViewPageConfig
     */
    protected $pageConfig;

    /**
     * @var Minification
     */
    protected $minification;

    /**
     * @var ViewAssetConfigInterface
     */
    protected $bundleConfig;

    /**
     * Config constructor.
     *
     * @param ViewElementContext $context
     * @param RequireJsConfig $config
     * @param RequireJsFileManager $fileManager
     * @param ViewPageConfig $pageConfig
     * @param ViewAssetConfigInterface $bundleConfig
     * @param Minification $minification
     * @param array $data
     */
    public function __construct(
        ViewElementContext $context,
        RequireJsConfig $config,
        RequireJsFileManager $fileManager,
        ViewPageConfig $pageConfig,
        ViewAssetConfigInterface $bundleConfig,
        Minification $minification,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->fileManager = $fileManager;
        $this->pageConfig = $pageConfig;
        $this->bundleConfig = $bundleConfig;
        $this->minification = $minification;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $after = RequireJsConfig::REQUIRE_JS_FILE_NAME;
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $assetCollection = $this->pageConfig->getAssetCollection();

        if ($this->minification->isEnabled('js')) {
            $minResolver = $this->fileManager->createMinResolverAsset();
            $assetCollection->insert(
                $minResolver->getFilePath(),
                $minResolver,
                $after
            );
            $after = $minResolver->getFilePath();
        }

        $requireJsMapConfig = $this->fileManager->createRequireJsMapConfigAsset();

        if ($requireJsMapConfig) {
            $urlResolverAsset = $this->fileManager->createUrlResolverAsset();
            $assetCollection->insert(
                $urlResolverAsset->getFilePath(),
                $urlResolverAsset,
                $after
            );
            $after = $urlResolverAsset->getFilePath();
            $assetCollection->insert(
                $requireJsMapConfig->getFilePath(),
                $requireJsMapConfig,
                $after
            );
            $after = $requireJsMapConfig->getFilePath();
        }

        if ($this->bundleConfig->isBundlingJsFiles($scopeType)) {

            $bundleAssets = $this->fileManager->createBundleJsPool();
            $staticAsset = $this->fileManager->createStaticJsAsset();

            /** @var \Magento\Framework\View\Asset\File $bundleAsset */
            if (!empty($bundleAssets) && $staticAsset !== false) {
                $bundleAssets = array_reverse($bundleAssets);
                foreach ($bundleAssets as $bundleAsset) {
                    $assetCollection->insert(
                        $bundleAsset->getFilePath(),
                        $bundleAsset,
                        $after
                    );
                }
                $assetCollection->insert(
                    $staticAsset->getFilePath(),
                    $staticAsset,
                    reset($bundleAssets)->getFilePath()
                );
                $after = $staticAsset->getFilePath();
            }
        }

        $requireJsConfig = $this->fileManager->createRequireJsConfigAsset();
        $assetCollection->insert(
            $requireJsConfig->getFilePath(),
            $requireJsConfig,
            $after
        );
        $requireJsMixinsConfig = $this->fileManager->createRequireJsMixinsAsset();
        $assetCollection->insert(
            $requireJsMixinsConfig->getFilePath(),
            $requireJsMixinsConfig,
            $after
        );

        return parent::_prepareLayout();
    }
}
