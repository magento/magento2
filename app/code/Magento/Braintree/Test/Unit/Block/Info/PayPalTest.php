<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block\Info;

class PayPalTest extends \PHPUnit_Framework_TestCase
{
    public function testGetChildHtml()
    {
        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getRequest'])
            ->getMock();

        $requestMock = $this->getMockBuilder('\Magento\Framework\App\Request')
            ->disableOriginalConstructor()
            ->setMethods(['getPost'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getPost')
            ->with('device_data')
            ->willReturn('device_data');

        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($requestMock);

        $configMock = $this->getMockBuilder('\Magento\Payment\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $info = $objectManagerHelper->getObject(
            'Magento\Braintree\Block\Info\PayPal',
            [
                'context' => $contextMock,
                'paymentConfig' => $configMock,
            ]
        );

        $result = $info->getChildHtml();
        $this->assertSame($result, "<input type='hidden' name='device_data' value='device_data'>");
    }
}
