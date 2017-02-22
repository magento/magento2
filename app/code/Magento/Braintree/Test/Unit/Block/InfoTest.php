<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block;

class InfoTest extends \PHPUnit_Framework_TestCase
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

        $requestMock->expects($this->at(0))
            ->method('getPost')
            ->with('payment')
            ->willReturn(['cc_token' => 'cc_token', 'store_in_vault' => 'store_in_vault']);

        $requestMock->expects($this->at(1))
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
            'Magento\Braintree\Block\Info',
            [
                'context' => $contextMock,
                'paymentConfig' => $configMock,
            ]
        );

        $result = $info->getChildHtml();
        $expected = "<input type='hidden' name='payment[cc_token]' value='cc_token'>"
            . "<input type='hidden' name='payment[store_in_vault]' value='store_in_vault'>"
            . "<input type='hidden' name='device_data' value='device_data'>";
        $this->assertSame($expected, $result);
    }
}
