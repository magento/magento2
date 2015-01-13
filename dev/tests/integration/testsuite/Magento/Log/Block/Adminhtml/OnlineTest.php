<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Block\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class OnlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetFilterFormHtml()
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout',
            ['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE]
        );
        /** @var $block \Magento\Log\Block\Adminhtml\Online */
        $block = $layout->createBlock('Magento\Log\Block\Adminhtml\Online', 'block');
        $this->assertNotEmpty($block->getFilterFormHtml());
    }
}
