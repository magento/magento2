<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block\Info;

class CcTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Payment\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentConfig = $this->createMock(\Magento\Payment\Model\Config::class);
        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $context = $this->createPartialMock(\Magento\Framework\View\Element\Template\Context::class, ['getLocaleDate']);
        $context->expects($this->any())
            ->method('getLocaleDate')
            ->willReturn($this->localeDate);
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
            ->willReturn($configCcTypes);
        $paymentInfo = $this->createPartialMock(\Magento\Payment\Model\Info::class, ['getCcType']);
        $paymentInfo->expects($this->any())
            ->method('getCcType')
            ->willReturn($ccType);
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
        $paymentInfo = $this->createPartialMock(\Magento\Payment\Model\Info::class, ['getCcExpMonth', 'getCcExpYear']);
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
        $paymentInfo = $this->createPartialMock(\Magento\Payment\Model\Info::class, ['getCcExpMonth']);
        $paymentInfo->expects($this->any())
            ->method('getCcExpMonth')
            ->willReturn($ccExpMonth);
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
        $paymentInfo = $this->createPartialMock(\Magento\Payment\Model\Info::class, ['getCcExpMonth', 'getCcExpYear']);
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
