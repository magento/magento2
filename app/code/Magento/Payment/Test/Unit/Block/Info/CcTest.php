<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Block\Info;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info\Cc;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\Info;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CcTest extends TestCase
{
    /**
     * @var Cc
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Config|MockObject
     */
    protected $paymentConfig;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDate;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->paymentConfig = $this->createMock(Config::class);
        $this->localeDate = $this->getMockForAbstractClass(TimezoneInterface::class);
        $context = $this->createPartialMock(Context::class, ['getLocaleDate']);
        $context->expects($this->any())
            ->method('getLocaleDate')
            ->willReturn($this->localeDate);
        $this->model = $this->objectManager->getObject(
            Cc::class,
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
            ->willReturn($configCcTypes);
        $paymentInfo = $this->getMockBuilder(Info::class)
            ->addMethods(['getCcType'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfo->expects($this->any())
            ->method('getCcType')
            ->willReturn($ccType);
        $this->model->setData('info', $paymentInfo);
        $this->assertEquals($expected, $this->model->getCcTypeName());
    }

    /**
     * @return array
     */
    public static function getCcTypeNameDataProvider()
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
        $paymentInfo = $this->getMockBuilder(Info::class)
            ->addMethods(['getCcExpMonth', 'getCcExpYear'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfo->expects($this->any())
            ->method('getCcExpMonth')
            ->willReturn($ccExpMonth);
        $paymentInfo->expects($this->any())
            ->method('getCcExpYear')
            ->willReturn($ccExpYear);
        $this->model->setData('info', $paymentInfo);
        $this->assertEquals($expected, $this->model->hasCcExpDate());
    }

    /**
     * @return array
     */
    public static function hasCcExpDateDataProvider()
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
        $paymentInfo = $this->getMockBuilder(Info::class)
            ->addMethods(['getCcExpMonth'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentInfo->expects($this->any())
            ->method('getCcExpMonth')
            ->willReturn($ccExpMonth);
        $this->model->setData('info', $paymentInfo);
        $this->assertEquals($expected, $this->model->getCcExpMonth());
    }

    /**
     * @return array
     */
    public static function ccExpMonthDataProvider()
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
        $paymentInfo = $this->getMockBuilder(Info::class)
            ->addMethods(['getCcExpMonth', 'getCcExpYear'])
            ->disableOriginalConstructor()
            ->getMock();
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
    public static function getCcExpDateDataProvider()
    {
        return [
            [3, 2015],
            [12, 2011],
            [01, 2036]
        ];
    }
}
