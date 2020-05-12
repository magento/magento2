<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\Listing\Columns\Date;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    const TEST_TIME = '2000-04-12 16:34:12';

    /**
     * @var MockObject
     */
    protected $contextMock;

    /**
     * @var Date
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $timezoneMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);

        $this->timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(
            Date::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends'
                    ],
                    'config' => [
                        'dataType' => 'testType'
                    ],
                    'name' => 'field_name',
                ],
                'timezone' => $this->timezoneMock
            ]
        );
    }

    public function testPrepareDataSource()
    {
        $item = ['test_data' => 'some_data', 'field_name' => self::TEST_TIME];

        $dateTime = new \DateTime(self::TEST_TIME);
        $this->timezoneMock->expects($this->once())
            ->method('date')
            ->willReturn($dateTime);

        $result = $this->model->prepareDataSource(['data' => ['items' => [$item]]]);
        $this->assertEquals(self::TEST_TIME, $result['data']['items'][0]['field_name']);
    }

    public function testPrepareDataSourceWithZeroDate()
    {
        $zeroDate = '0000-00-00 00:00:00';
        $item = ['test_data' => 'some_data', 'field_name' => $zeroDate];
        $this->timezoneMock->expects($this->never())->method('date');

        $result = $this->model->prepareDataSource(['data' => ['items' => [$item]]]);
        $this->assertEquals($zeroDate, $result['data']['items'][0]['field_name']);
    }
}
