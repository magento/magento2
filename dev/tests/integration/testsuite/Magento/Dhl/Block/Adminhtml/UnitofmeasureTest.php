<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Block\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class UnitofmeasureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Dhl\Block\Adminhtml\Unitofmeasure */
        $block = $layout->createBlock(\Magento\Dhl\Block\Adminhtml\Unitofmeasure::class);
        $this->assertNotEmpty($block->toHtml());
    }
}
