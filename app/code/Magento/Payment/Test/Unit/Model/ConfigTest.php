<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Payment\Test\Unit\Model;

use \Magento\Payment\Model\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConfigTest extends \PHPUnit_Framework_TestCase
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
    protected $paymentMethodsList = [
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
        $this->scopeConfig = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );
        $this->paymentMethodFactory = $this->getMock('Magento\Payment\Model\Method\Factory', [], [], '', false);
        $this->localeResolver = $this->getMock('Magento\Framework\Locale\ResolverInterface', [], [], '', false);
        $this->dataStorage = $this->getMock('Magento\Framework\Config\DataInterface', [], [], '', false);
        $this->date = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->config = $this->objectManagerHelper->getObject(
            'Magento\Payment\Model\Config',
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
     * @param bool $isActive
     * @dataProvider getActiveMethodsDataProvider
     */
    public function testGetActiveMethods($isActive)
    {
        $abstractMethod = $this->getMockBuilder(
            'Magento\Payment\Model\Method\AbstractMethod'
        )->disableOriginalConstructor()->setMethods(['setId', 'setStore', 'getConfigData'])->getMock();
        $this->scopeConfig->expects($this->once())->method('getValue')->with(
            'payment', ScopeInterface::SCOPE_STORE, null
        )->will($this->returnValue($this->paymentMethodsList));
        $this->paymentMethodFactory->expects($this->once())->method('create')->with(
            $this->paymentMethodsList['active_method']['model']
        )->will($this->returnValue($abstractMethod));
        $abstractMethod->expects($this->any())->method('setId')->with('active_method')->will(
            $this->returnValue($abstractMethod)
        );
        $abstractMethod->expects($this->any())->method('setStore')->with(null);
        $abstractMethod->expects($this->any())
            ->method('getConfigData')
            ->with('active', $this->isNull())
            ->will($this->returnValue($isActive));
        $this->assertEquals($isActive ? ['active_method' => $abstractMethod] : [], $this->config->getActiveMethods());
    }

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
