<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme data helper
 */
namespace Magento\Theme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Layout\ProcessorFactory;
use Magento\Framework\View\Layout\ProcessorInterface;

class Theme extends AbstractHelper
{
    /**
     * Layout merge factory
     *
     * @var ProcessorFactory
     */
    protected $_layoutProcessorFactory;

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @param Context $context
     * @param ProcessorFactory $layoutProcessorFactory
     * @param Repository $assetRepo
     */
    public function __construct(
        Context $context,
        ProcessorFactory $layoutProcessorFactory,
        Repository $assetRepo
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
     * @param ThemeInterface $theme
     * @return LocalInterface[]
     */
    public function getCssAssets($theme)
    {
        /** @var ProcessorInterface $layoutProcessor */
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
