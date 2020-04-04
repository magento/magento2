<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Method\Factory;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Factory|MockObject
     */
    private $paymentMethodFactoryMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var DataInterface|MockObject
     */
    private $dataStorageMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateMock;

    /**
     * Test payments list
     *
     * @var array
     */
    private $paymentMethodsList = [
        'not_active_method' => ['active' => 0],
        'active_method_no_model' => ['active' => 1],
        'active_method' => ['active' => 1, 'model' => 'model_name'],
    ];

    /**
     * Expected months list
     *
     * @var array
     */
    private $expectedMonthList = [
        1 => '01 - January',
        '02 - February',
        '03 - March',
        '04 - April',
        '05 - May',
        '06 - June',
        '07 - July',
        '08 - August',
        '09 - September',
        '10 - October',
        '11 - November',
        '12 - December',
    ];

    /**
     * Current year value in ISO
     */
    const CURRENT_YEAR = '2250';

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->paymentMethodFactoryMock = $this->createMock(Factory::class);
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        $this->dataStorageMock = $this->createMock(DataInterface::class);
        $this->dateMock = $this->createMock(DateTime::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'paymentMethodFactory' => $this->paymentMethodFactoryMock,
                'localeResolver' => $this->localeResolverMock,
                'dataStorage' => $this->dataStorageMock,
                'date' => $this->dateMock
            ]
        );
    }

    /**
     * @covers       \Magento\Payment\Model\Config::getActiveMethods
     * @param bool $isActive
     * @dataProvider getActiveMethodsDataProvider
     */
    public function testGetActiveMethods($isActive)
    {
        $adapter = $this->createMock(MethodInterface::class);
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with('payment', ScopeInterface::SCOPE_STORE, null)
            ->willReturn($this->paymentMethodsList);
        $this->paymentMethodFactoryMock->expects(static::once())
            ->method('create')
            ->with($this->paymentMethodsList['active_method']['model'])
            ->willReturn($adapter);
        $adapter->expects(static::once())
            ->method('setStore')
            ->with(null);
        $adapter->expects(static::once())
            ->method('getConfigData')
            ->with('active', static::isNull())
            ->willReturn($isActive);
        static::assertEquals($isActive ? ['active_method' => $adapter] : [], $this->config->getActiveMethods());
    }

    /**
     * @return array
     */
    public function getActiveMethodsDataProvider()
    {
        return [[true], [false]];
    }

    public function testGetCcTypes()
    {
        $expected = [];
        $this->dataStorageMock->expects($this->once())->method('get')->with('credit_cards')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->config->getCcTypes());
    }

    public function testGetMethodsInfo()
    {
        $expected = [];
        $this->dataStorageMock->expects($this->once())->method('get')->with('methods')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->config->getMethodsInfo());
    }

    public function testGetGroups()
    {
        $expected = [];
        $this->dataStorageMock->expects($this->once())->method('get')->with('groups')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->config->getGroups());
    }

    public function testGetMonths()
    {
        $this->localeResolverMock->expects($this->once())->method('getLocale')->willReturn('en_US');
        $this->assertEquals($this->expectedMonthList, $this->config->getMonths());
    }

    public function testGetYears()
    {
        $this->dateMock->expects($this->once())->method('date')->with('Y')->will($this->returnValue(self::CURRENT_YEAR));
        $this->assertEquals($this->_getPreparedYearsList(), $this->config->getYears());
    }

    /**
     * Generates expected years list
     *
     * @return array
     */
    private function _getPreparedYearsList()
    {
        $expectedYearsList = [];
        for ($index = 0; $index <= Config::YEARS_RANGE; $index++) {
            $year = (int)self::CURRENT_YEAR + $index;
            $expectedYearsList[$year] = $year;
        }
        return $expectedYearsList;
    }
}
