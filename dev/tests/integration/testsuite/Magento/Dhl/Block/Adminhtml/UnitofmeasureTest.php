<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Dhl\Block\Adminhtml\Unitofmeasure */
        $block = $layout->createBlock('Magento\Dhl\Block\Adminhtml\Unitofmeasure');
        $this->assertNotEmpty($block->toHtml());
    }
}
