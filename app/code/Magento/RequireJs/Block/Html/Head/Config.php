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
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\RequireJs\Config $config,
        \Magento\RequireJs\Model\FileManager $fileManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->fileManager = $fileManager;
        $this->pageConfig = $pageConfig;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $asset = $this->fileManager->createRequireJsAsset();
        $this->pageConfig->getAssetCollection()->add($asset->getFilePath(), $asset);
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
