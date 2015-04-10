<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Block\Html\Head;

use Magento\Framework\RequireJs\Config as RequireJsConfig;

/**
 * Block responsible for including RequireJs config on the page
 */
class Config extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var RequireJsConfig
     */
    private $config;

    /**
     * @var \Magento\RequireJs\Model\FileManager
     */
    private $fileManager;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param RequireJsConfig $config
     * @param \Magento\RequireJs\Model\FileManager $fileManager
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Asset\ConfigInterface $bundleConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        RequireJsConfig $config,
        \Magento\RequireJs\Model\FileManager $fileManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Asset\ConfigInterface $bundleConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->fileManager = $fileManager;
        $this->pageConfig = $pageConfig;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $after = RequireJsConfig::REQUIRE_JS_FILE_NAME;
        $requireJsConfig = $this->fileManager->createRequireJsConfigAsset();
        $assetCollection = $this->pageConfig->getAssetCollection();

        if ($this->bundleConfig->isBundlingJsFiles()) {
            $bundleAssets = $this->fileManager->createBundleJsPool();
            $staticAsset = $this->fileManager->createStaticJsAsset();

            /** @var \Magento\Framework\View\Asset\File $bundleAsset */
            if (!empty($bundleAssets) && $staticAsset !== false) {
                $bundleAssets = array_reverse($bundleAssets);
                foreach ($bundleAssets as $bundleAsset) {
                    $assetCollection->insert(
                        $bundleAsset->getFilePath(),
                        $bundleAsset,
                        RequireJsConfig::REQUIRE_JS_FILE_NAME
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

        $assetCollection->insert(
            $requireJsConfig->getFilePath(),
            $requireJsConfig,
            $after
        );

        return parent::_prepareLayout();
    }

    /**
     * Include base RequireJs configuration necessary for working with Magento application
     *
     * @return string|void
     */
    protected function _toHtml()
    {
        return "<script type=\"text/javascript\">\n"
            . $this->config->getBaseConfig()
            . "</script>\n";
    }
}
