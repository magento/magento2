<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Page\Config\ClientSideLessCompilation;

use Magento\Framework\View\Page\Config;

/**
 * Page config Renderer model
 */
class Renderer extends Config\Renderer
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param Config $pageConfig
     * @param \Magento\Framework\View\Asset\MergeService $assetMergeService
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        Config $pageConfig,
        \Magento\Framework\View\Asset\MergeService $assetMergeService,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;

        parent::__construct(
            $pageConfig,
            $assetMergeService,
            $urlBuilder,
            $escaper,
            $string,
            $logger
        );
    }

    /**
     * @param string $contentType
     * @param string $attributes
     * @return string
     */
    protected function addDefaultAttributes($contentType, $attributes)
    {
        switch ($contentType) {
            case 'css':
                return ' rel="stylesheet/less" type="text/css" ' . ($attributes ?: ' media="all"');
                break;

        }

        return parent::addDefaultAttributes($contentType, $attributes);
    }

    /**
     * Returns rendered HTML for all Assets (CSS before)
     *
     * @param array $resultGroups
     *
     * @return string
     */
    public function renderAssets($resultGroups = [])
    {
        return parent::renderAssets($this->renderLessJsScripts($resultGroups));
    }

    /**
     * Injecting less.js compiler
     *
     * @param array $resultGroups
     *
     * @return mixed
     */
    private function renderLessJsScripts($resultGroups)
    {
        // less js have to be injected before any *.js in developer mode
        $lessJsConfigAsset = $this->assetRepo->createAsset('less/config.less.js');
        $resultGroups['js'] .= sprintf('<script src="%s"></script>' . "\n", $lessJsConfigAsset->getUrl());
        $lessJsAsset = $this->assetRepo->createAsset('less/less.min.js');
        $resultGroups['js'] .= sprintf('<script src="%s"></script>' . "\n", $lessJsAsset->getUrl());

        return $resultGroups;
    }

    /**
     * Render HTML tags referencing corresponding URLs
     *
     * @param string $template
     * @param array $assets
     * @return string
     */
    protected function renderAssetHtml($template, $assets)
    {
        $result = '';
        try {
            foreach ($assets as $asset) {
                /** @var $asset \Magento\Framework\View\Asset\File */
                if ($asset instanceof \Magento\Framework\View\Asset\File
                    && $asset->getSourceUrl() != $asset->getUrl()
                ) {
                    $attributes = $this->addDefaultAttributes('less', []);
                    $groupTemplate = $this->getAssetTemplate($asset->getContentType(), $attributes);
                    $result .= sprintf($groupTemplate, $asset->getSourceUrl());
                } else {
                    $result .= sprintf($template, $asset->getUrl());
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            $result .= sprintf($template, $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']));
        }
        return $result;
    }
}
