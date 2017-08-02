<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme data helper
 */
namespace Magento\Theme\Helper;

/**
 * Class \Magento\Theme\Helper\Theme
 *
 * @since 2.0.0
 */
class Theme extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Layout merge factory
     *
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     * @since 2.0.0
     */
    protected $_layoutProcessorFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\Layout\ProcessorFactory $layoutProcessorFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_layoutProcessorFactory = $layoutProcessorFactory;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context);
    }

    /**
     * Get CSS files of a given theme
     *
     * Returns an associative array of local assets with FileId used as keys:
     * array('Magento_Catalog::widgets.css' => \Magento\Framework\View\Asset\LocalInterface)
     * The array will be sorted by keys
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return \Magento\Framework\View\Asset\LocalInterface[]
     * @since 2.0.0
     */
    public function getCssAssets($theme)
    {
        /** @var $layoutProcessor \Magento\Framework\View\Layout\ProcessorInterface */
        $layoutProcessor = $this->_layoutProcessorFactory->create(['theme' => $theme]);
        $layoutElement = $layoutProcessor->getFileLayoutUpdatesXml();

        /**
         * XPath selector to get CSS files from layout added for HEAD block directly
         */
        $xpathSelectorBlocks = '//block[@class="Magento\Theme\Block\Html\Head"]' .
            '/block[@class="Magento\Theme\Block\Html\Head\Css"]/arguments/argument[@name="file"]';

        /**
         * XPath selector to get CSS files from layout added for HEAD block using reference
         */
        $xpathSelectorRefs = '//referenceBlock[@name="head"]' .
            '/block[@class="Magento\Theme\Block\Html\Head\Css"]/arguments/argument[@name="file"]';

        $elements = array_merge(
            $layoutElement->xpath($xpathSelectorBlocks) ?: [],
            $layoutElement->xpath($xpathSelectorRefs) ?: []
        );

        $params = [
            'area'       => $theme->getArea(),
            'themeModel' => $theme,
        ];

        $result = [];
        foreach ($elements as $fileId) {
            $fileId = (string)$fileId;
            $result[$fileId] = $this->_assetRepo->createAsset($fileId, $params);
        }
        ksort($result);
        return $result;
    }
}
