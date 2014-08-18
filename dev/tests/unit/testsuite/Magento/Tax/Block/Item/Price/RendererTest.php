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
namespace Magento\Tax\Block\Item\Price;

use Magento\Framework\Object;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->taxHelper = $this->getMockBuilder('\Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([
                'displayCartPriceExclTax', 'displayCartBothPrices', 'displayCartPriceInclTax'
            ])
            ->getMock();

        $this->renderer = $objectManager->getObject(
            '\Magento\Tax\Block\Item\Price\Renderer',
            [
                'taxHelper' => $this->taxHelper,
            ]
        );
    }

    public function testDisplayPriceInclTax()
    {
        $this->taxHelper->expects($this->once())
            ->method('displayCartPriceInclTax');

        $this->renderer->displayPriceInclTax();
    }

    public function testDisplayPriceExclTax()
    {
        $this->taxHelper->expects($this->once())
            ->method('displayCartPriceExclTax');

        $this->renderer->displayPriceExclTax();
    }

    public function testDisplayBothPrices()
    {
        $this->taxHelper->expects($this->once())
            ->method('displayCartBothPrices');

        $this->renderer->displayBothPrices();
    }
}
