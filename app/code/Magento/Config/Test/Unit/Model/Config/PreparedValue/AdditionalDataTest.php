<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\PreparedValue;

use Magento\Config\Model\Config\Backend\Currency\Allow;
use Magento\Config\Model\Config\Backend\Currency\Base;
use Magento\Config\Model\Config\Backend\Currency\DefaultCurrency;
use Magento\Config\Model\Config\PreparedValue\AdditionalData;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for AdditionalData.
 *
 * @see AdditionalData
 */
class AdditionalDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeConfigInterface|Mock
     */
    private $scopeConfigMock;

    /**
     * @var AdditionalData
     */
    private $model;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->model = new AdditionalData($this->scopeConfigMock);
    }

    /**
     * Test applying data to Allow currency model
     */
    public function testApplyAllowCurrency()
    {
        $currencies = 'EUR,USD';
        $valueMock = $this->getMockBuilder(Allow::class)
            ->setMethods(['getScope', 'getScopeId', 'addData', 'getPath', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $valueMock->expects($this->once())
            ->method('getPath')
            ->willReturn(Currency::XML_PATH_CURRENCY_ALLOW);
        $valueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($currencies);
        $valueMock->expects($this->never())
            ->method('getScope');
        $valueMock->expects($this->never())
            ->method('getScopeId');
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');
        $valueMock->expects($this->once())
            ->method('addData')
            ->with([
                'groups' => [
                    'options' => [
                        'fields' => [
                            'allow' => ['value' => explode(',', $currencies)]
                        ]
                    ]
                ]
            ]);

        $this->model->apply($valueMock);
    }

    /**
     * Test applying data to non currency model
     */
    public function testApplyNonCurrencyValue()
    {
        $methods = ['getScope', 'getScopeId', 'addData', 'getPath', 'getValue'];
        $valueMock = $this->getMockBuilder(Value::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        foreach ($methods as $methodName) {
            $valueMock->expects($this->never())
                ->method($methodName);
        }
        $this->scopeConfigMock->expects($this->never())
            ->method('getValue');

        $this->model->apply($valueMock);
    }

    /**
     * Test applying data to Default and Base currency models
     *
     * @param string $class
     * @dataProvider applyCurrencyDataProvider
     */
    public function testApplyCurrency($class)
    {
        $scope = 'scope';
        $scopeCode= 'scopeCode';
        $currencies = 'EUR,USD';
        $valueMock = $this->getMockBuilder($class)
            ->setMethods(['getScope', 'getScopeId', 'addData', 'getPath', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $valueMock->expects($this->once())
            ->method('getScope')
            ->willReturn($scope);
        $valueMock->expects($this->once())
            ->method('getScopeId')
            ->willReturn($scopeCode);
        $valueMock->expects($this->never())
            ->method('getValue');
        $valueMock->expects($this->once())
            ->method('addData')
            ->with([
                'groups' => [
                    'options' => [
                        'fields' => [
                            'allow' => ['value' => explode(',', $currencies)]
                        ]
                    ]
                ]
            ]);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Currency::XML_PATH_CURRENCY_ALLOW, $scope, $scopeCode)
            ->willReturn($currencies);

        $this->model->apply($valueMock);
    }

    /**
     * @return array
     */
    public function applyCurrencyDataProvider()
    {
        return [
            [DefaultCurrency::class],
            [Base::class],
        ];
    }
}
