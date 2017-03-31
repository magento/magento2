<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;

/**
 * @magentoAppArea adminhtml
 */
class ContentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUploader()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content */
        $block = $layout->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content::class,
            'block'
        );

        $this->assertInstanceOf(\Magento\Backend\Block\Media\Uploader::class, $block->getUploader());
    }
}
