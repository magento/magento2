<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model\Customer\Autentication;

class ConfigPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Checkout\Model\Customer\Autentication\ConfigPlugin
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface');
        $this->model = $objectManager->getObject(
            '\Magento\Checkout\Model\Customer\Autentication\ConfigPlugin',
            ['urlBuilder' => $this->urlBuilderMock]
        );
    }

    public function testAfterGetConfig()
    {
        $result = ['key' => 'value'];
        $expectedResult = [
            'key' => 'value',
            'checkoutUrl' => 'checkout/url'
        ];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('checkout')
            ->willReturn($expectedResult['checkoutUrl']);
        $response = $this->model->afterGetConfig(
            $this->getMock('\Magento\Customer\Block\Account\AuthenticationPopup', [], [], '', false),
            $result
        );
        $this->assertEquals($expectedResult, $response);
    }
}
