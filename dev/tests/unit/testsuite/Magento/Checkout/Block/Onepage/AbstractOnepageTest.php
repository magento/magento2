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
namespace Magento\Checkout\Block\Onepage;

class AbstractOnepageTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManager;

    protected $shippingBlock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetShippingPriceHtml()
    {
        $shippingRateMock = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address\Rate')
            ->disableOriginalConstructor()
            ->getMock();

        $shippingPriceHtml = "$3.25 ($3.56 Incl Tax)";

        $priceBlockMock = $this->getMockBuilder('\Magento\Checkout\Block\Shipping\Price')
            ->disableOriginalConstructor()
            ->setMethods(['setShippingRate', 'toHtml'])
            ->getMock();

        $priceBlockMock->expects($this->once())
            ->method('setShippingRate')
            ->with($shippingRateMock);

        $priceBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($shippingPriceHtml));

        $layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('checkout.shipping.price')
            ->will($this->returnValue($priceBlockMock));

        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        /** @var \Magento\Checkout\Block\Onepage\AbstractOnepage $onepage */
        $onepage = $this->objectManager->getObject(
            '\Magento\Checkout\Block\Cart\Shipping',
            ['context' => $contextMock]
        );

        $this->assertEquals($shippingPriceHtml, $onepage->getShippingPriceHtml($shippingRateMock));
    }
}
