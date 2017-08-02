<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block\Html\Head;

use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\Translate\Inline as Inline;

/**
 * Block responsible for including Inline Translation config on the page
 *
 * @api
 * @since 2.0.0
 */
class Config extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Translation\Model\FileManager
     * @since 2.0.0
     */
    private $fileManager;

    /**
     * @var \Magento\Framework\View\Page\Config
     * @since 2.0.0
     */
    protected $pageConfig;

    /**
     * @var Inline
     * @since 2.0.0
     */
    private $inline;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param RequireJsConfig $config
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Translation\Model\FileManager $fileManager
     * @param Inline $inline
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Translation\Model\FileManager $fileManager,
        Inline $inline,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageConfig = $pageConfig;
        $this->fileManager = $fileManager;
        $this->inline = $inline;
    }

    /**
     * Include RequireJs configuration as an asset on the page
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addInlineTranslationConfig();

        return parent::_prepareLayout();
    }

    /**
     * Include RequireJs inline translation configuration as an asset on the page
     * @return void
     * @since 2.0.0
     */
    private function addInlineTranslationConfig()
    {
        if ($this->inline->isAllowed()) {
            $after = RequireJsConfig::REQUIRE_JS_FILE_NAME;
            $tConfig = $this->fileManager->createTranslateConfigAsset();
            $assetCollection = $this->pageConfig->getAssetCollection();
            $assetCollection->insert(
                $tConfig->getFilePath(),
                $tConfig,
                $after
            );
        }
    }
}
