<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType;

use Magento\Ui\Component\Form\Element\DataType\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDate;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolver;

    /**
     * @var Date
     */
    private $model;

    public function setUp()
    {
        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getProcessor')
            ->willReturn($processorMock);

        $this->localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->getMockForAbstractClass();

        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->getMockForAbstractClass();
    }

    public function testPrepareWithTimeOffset()
    {
        $this->model = new Date(
            $this->context,
            $this->localeDate,
            $this->localeResolver,
            [],
            [
                'config' => [
                    'timeOffset' => 1,
                ],
            ]
        );

        $localeDateFormat = 'dd/MM/y';

        $this->localeDate->expects($this->once())
            ->method('getDateFormat')
            ->willReturn($localeDateFormat);

        $this->model->prepare();

        $config = $this->model->getConfig();
        $this->assertTrue(is_array($config));

        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('dateFormat', $config['options']);
        $this->assertEquals($localeDateFormat, $config['options']['dateFormat']);

        $this->assertArrayHasKey('outputDateFormat', $config);
        $this->assertEquals($localeDateFormat, $config['outputDateFormat']);
    }

    public function testPrepareWithoutTimeOffset()
    {
        $defaultDateFormat = 'MM/dd/y';

        $this->model = new Date(
            $this->context,
            $this->localeDate,
            $this->localeResolver,
            [],
            [
                'config' => [
                    'options' => [
                        'dateFormat' => $defaultDateFormat,
                    ],
                    'outputDateFormat' => $defaultDateFormat,
                ],
            ]
        );

        $localeDateFormat = 'dd/MM/y';

        $this->localeDate->expects($this->once())
            ->method('getDateFormat')
            ->willReturn($localeDateFormat);
        $this->localeDate->expects($this->once())
            ->method('getConfigTimezone')
            ->willReturn('America/Los_Angeles');

        $this->model->prepare();

        $config = $this->model->getConfig();
        $this->assertTrue(is_array($config));

        $this->assertArrayHasKey('timeOffset', $config);

        $this->assertArrayHasKey('options', $config);
        $this->assertArrayHasKey('dateFormat', $config['options']);
        $this->assertEquals($localeDateFormat, $config['options']['dateFormat']);

        $this->assertArrayHasKey('outputDateFormat', $config);
        $this->assertEquals($localeDateFormat, $config['outputDateFormat']);
    }
}
