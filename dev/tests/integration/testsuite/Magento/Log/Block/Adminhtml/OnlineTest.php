<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
