<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Block\Html\Head;

/**
 * Block responsible for including RequireJs config on the page
 */
class Config extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\RequireJs\Config
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
     * @param \Magento\Framework\RequireJs\Config $config
     * @param \Magento\RequireJs\Model\FileManager $fileManager
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Asset\ConfigInterface $bundleConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\RequireJs\Config $config,
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
        $requireJsConfig = $this->fileManager->createRequireJsConfigAsset();
        $group = $this->pageConfig->getAssetCollection()->getGroupByContentType('js');

        if ($group && $this->bundleConfig->isBundlingJsFiles()) {

            $after = \Magento\Framework\RequireJs\Config::REQUIRE_JS_FILE_NAME;
            /** @var \Magento\Framework\View\Asset\File $bundleAsset */
            foreach ($this->fileManager->createBundleJsPool() as $bundleAsset) {
                $group->addAfter($bundleAsset->getFilePath(), $bundleAsset, $after);
                $after = $bundleAsset->getFilePath();
            }

            $staticAsset = $this->fileManager->createStaticJsAsset();
            if ($staticAsset !== false) {
                $group->addAfter($staticAsset->getFilePath(), $staticAsset, $after);
            }
        }
        if ($group) {
            $group->addAfter(
                $requireJsConfig->getFilePath(),
                $requireJsConfig,
                \Magento\Framework\RequireJs\Config::REQUIRE_JS_FILE_NAME
            );
        } else {
            $this->pageConfig->getAssetCollection()->add(
                $requireJsConfig->getFilePath(),
                $requireJsConfig
            );
        }
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
