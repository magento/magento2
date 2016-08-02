<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block\Info;

class CcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Block\Info\Cc
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Payment\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentConfig = $this->getMock(\Magento\Payment\Model\Config::class, [], [], '', false);
        $this->localeDate = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class,
            [],
            [],
            '',
            false
        );
        $context = $this->getMock(
            \Magento\Framework\View\Element\Template\Context::class,
            ['getLocaleDate'],
            [],
            '',
            false
        );
        $context->expects($this->any())
            ->method('getLocaleDate')
            ->will($this->returnValue($this->localeDate));
        $this->model = $this->objectManager->getObject(
            \Magento\Payment\Block\Info\Cc::class,
            [
                'paymentConfig' => $this->paymentConfig,
                'context' => $context
            ]
        );
    }

    /**
     * @dataProvider getCcTypeNameDataProvider
     */
    public function testGetCcTypeName($configCcTypes, $ccType, $expected)
    {
        $this->paymentConfig->expects($this->any())
            ->method('getCcTypes')
            ->will($this->returnValue($configCcTypes));
        $paymentInfo = $this->getMock(\Magento\Payment\Model\Info::class, ['getCcType'], [], '', false);
        $paymentInfo->expects($this->any())
            ->method('getCcType')
            ->will($this->returnValue($ccType));
        $this->model->setData('info', $paymentInfo);
        $this->assertEquals($expected, $this->model->getCcTypeName());
    }

    /**
     * @return array
     */
    public function getCcTypeNameDataProvider()
    {
        return [
            [['VS', 'MC', 'JCB'], 'JCB', 'JCB'],
            [['VS', 'MC', 'JCB'], 'BNU', 'BNU'],
            [['VS', 'MC', 'JCB'], null, 'N/A'],
        ];
    }

    /**
     * @dataProvider hasCcExpDateDataProvider
     */
    public function testHasCcExpDate($ccExpMonth, $ccExpYear, $expected)
    {
        $paymentInfo = $this->getMock(
            \Magento\Payment\Model\Info::class,
            ['getCcExpMonth', 'getCcExpYear'],
            [],
            '',
            false
        );
        $paymentInfo->expects($this->any())
            ->method('getCcExpMonth')
            ->will($this->returnValue($ccExpMonth));
        $paymentInfo->expects($this->any())
            ->method('getCcExpYear')
            ->will($this->returnValue($ccExpYear));
        $this->model->setData('info', $paymentInfo);
        $this->assertEquals($expected, $this->model->hasCcExpDate());
    }

    /**
     * @return array
     */
    public function hasCcExpDateDataProvider()
    {
        return [
            [0, 1, true],
            [1, 0, true],
            [0, 0, false]
        ];
    }

    /**
     * @dataProvider ccExpMonthDataProvider
     */
    public function testGetCcExpMonth($ccExpMonth, $expected)
    {
        $paymentInfo = $this->getMock(\Magento\Payment\Model\Info::class, ['getCcExpMonth'], [], '', false);
        $paymentInfo->expects($this->any())
            ->method('getCcExpMonth')
            ->will($this->returnValue($ccExpMonth));
        $this->model->setData('info', $paymentInfo);
        $this->assertEquals($expected, $this->model->getCcExpMonth());
    }

    /**
     * @return array
     */
    public function ccExpMonthDataProvider()
    {
        return [
            [2, '02'],
            [12, '12']
        ];
    }

    /**
     * @dataProvider getCcExpDateDataProvider
     */
    public function testGetCcExpDate($ccExpMonth, $ccExpYear)
    {
        $paymentInfo = $this->getMock(
            \Magento\Payment\Model\Info::class,
            ['getCcExpMonth', 'getCcExpYear'],
            [],
            '',
            false
        );
        $paymentInfo
            ->expects($this->any())
            ->method('getCcExpMonth')
            ->willReturn($ccExpMonth);
        $paymentInfo
            ->expects($this->any())
            ->method('getCcExpYear')
            ->willReturn($ccExpYear);
        $this->model->setData('info', $paymentInfo);

        $this->localeDate
            ->expects($this->exactly(2))
            ->method('getConfigTimezone')
            ->willReturn('America/Los_Angeles');

        $this->assertEquals($ccExpYear, $this->model->getCcExpDate()->format('Y'));
        $this->assertEquals($ccExpMonth, $this->model->getCcExpDate()->format('m'));
    }

    /**
     * @return array
     */
    public function getCcExpDateDataProvider()
    {
        return [
            [3, 2015],
            [12, 2011],
            [01, 2036]
        ];
    }
}
