<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rss\Block\Catalog;

/**
 * Test for rendering price html in rss templates
 *
 */
class AbstractCatalogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test renderPriceHtml function
     */
    public function testRenderPriceHtml()
    {
        $priceHtmlForTest = '<html>Price is 10 for example</html>';
        $templateContextMock = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $httpContextMock = $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false);
        $helperMock = $this->getMock('Magento\Catalog\Helper\Data', [], [], '', false);
        $productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            [],
            '',
            true,
            true,
            true,
            ['getBlock']
        );
        $priceRendererMock = $this->getMock('Magento\Framework\Pricing\Render', ['render'], [], '', false);

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($priceRendererMock));
        $priceRendererMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue($priceHtmlForTest));

        $block = new \Magento\Rss\Block\Catalog\AbstractCatalog(
            $templateContextMock,
            $httpContextMock,
            $helperMock
        );
        $block->setLayout($layoutMock);

        $this->assertEquals($priceHtmlForTest, $block->renderPriceHtml($productMock, true));
    }
}
