<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Block;

class DatajsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Braintree\Block\Datajs
     */
    protected $dataJs;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getRequest', 'getScopeConfig'])
            ->getMock();

        $this->requestMock = $this->getMockBuilder('\Magento\Framework\App\Request')
            ->disableOriginalConstructor()
            ->setMethods(['getControllerName', 'getActionName'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->configMock = $this->getMockBuilder('\Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->configMock);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataJs = $objectManagerHelper->getObject(
            'Magento\Braintree\Block\Datajs',
            [
                'context' => $this->contextMock,
            ]
        );
    }

    public function testGetJsSrc()
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('payment/braintree/data_js', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn('data.js');

        $this->dataJs->getJsSrc();
    }

    public function testGetMerchantId()
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('payment/braintree/merchant_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->willReturn('merchant_id');

        $this->dataJs->getMerchantId();
    }

    /**
     * @param string $expected
     * @param string $controllerName
     * @param string $actionName
     * @dataProvider dataProviderGetFormId
     */
    public function testGetFormId($expected, $controllerName, $actionName)
    {
        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn($controllerName);

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($actionName);

        $result = $this->dataJs->getFormId();

        $this->assertSame($result, $expected);
    }

    /**
     * @return array
     */
    public function dataProviderGetFormId()
    {
        return [
            ['form-validate', 'creditcard', 'newcard'],
            ['form-validate', 'creditcard', 'edit'],
            ['delete-form', 'creditcard', 'delete'],
            ['multishipping-billing-form', 'multishipping', ''],
            ['edit_form', 'order_create', ''],
            ['co-payment-form', '', '']
        ] ;
    }
}
