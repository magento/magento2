<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Model\Plugin;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage as OriginalBlock;
use Magento\Framework\View\Element\Template;

/**
 * BaseImage decorator
 */
class BaseImage
{
    /**
     * Element output template
     */
    const ELEMENT_OUTPUT_TEMPLATE = 'Magento_ProductVideo::product/edit/base_image.phtml';

    /**
     * @param OriginalBlock $baseImage
     * @param Template $block
     * @return Template
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAssignBlockVariables(OriginalBlock $baseImage, Template $block)
    {
        $block->assign([
            'videoPlaceholderText' => __('Click here to add videos.'),
            'addVideoTitle' => __('New Video'),
        ]);

        return $block;
    }

    /**
     * @param OriginalBlock $baseImage
     * @param Template $block
     * @return Template
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateElementHtmlOutputBlock(OriginalBlock $baseImage, Template $block)
    {
        $block->setTemplate(self::ELEMENT_OUTPUT_TEMPLATE);
        return $block;
    }
}
