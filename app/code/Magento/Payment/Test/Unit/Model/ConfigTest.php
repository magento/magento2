<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Model\Config */
    protected $config;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var \Magento\Payment\Model\Method\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentMethodFactory;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeResolver;

    /** @var \Magento\Framework\Config\DataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataStorage;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $date;

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
     * List of test month
     *
     * @var array
     */
    protected $monthList = [
        1 => 'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];

    /**
     * Expected months list
     *
     * @var array
     */
    protected $expectedMonthList = [
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
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->paymentMethodFactory = $this->createMock(\Magento\Payment\Model\Method\Factory::class);
        $this->localeResolver = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $this->dataStorage = $this->createMock(\Magento\Framework\Config\DataInterface::class);
        $this->date = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            \Magento\Payment\Model\Config::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'paymentMethodFactory' => $this->paymentMethodFactory,
                'localeResolver' => $this->localeResolver,
                'dataStorage' => $this->dataStorage,
                'date' => $this->date
            ]
        );
    }

    /**
     * @covers \Magento\Payment\Model\Config::getActiveMethods
     * @param bool $isActive
     * @dataProvider getActiveMethodsDataProvider
     */
    public function testGetActiveMethods($isActive)
    {
        $adapter = $this->createMock(MethodInterface::class);
        $this->scopeConfig->expects(static::once())
            ->method('getValue')
            ->with('payment', ScopeInterface::SCOPE_STORE, null)
            ->willReturn($this->paymentMethodsList);
        $this->paymentMethodFactory->expects(static::once())
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
        $this->dataStorage->expects($this->once())->method('get')->with('credit_cards')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->config->getCcTypes());
    }

    public function testGetMethodsInfo()
    {
        $expected = [];
        $this->dataStorage->expects($this->once())->method('get')->with('methods')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->config->getMethodsInfo());
    }

    public function testGetGroups()
    {
        $expected = [];
        $this->dataStorage->expects($this->once())->method('get')->with('groups')->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->config->getGroups());
    }

    public function testGetMonths()
    {
        $this->localeResolver->expects($this->once())->method('getLocale')->willReturn('en_US');
        $this->assertEquals($this->expectedMonthList, $this->config->getMonths());
    }

    public function testGetYears()
    {
        $this->date->expects($this->once())->method('date')->with('Y')->will($this->returnValue(self::CURRENT_YEAR));
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
