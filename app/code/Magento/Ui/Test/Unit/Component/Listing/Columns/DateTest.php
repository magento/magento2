<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class DateTest
 */
class DateTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TIME = '2000-04-12 16:34:12';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Ui\Component\Listing\Columns\Date
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $timezoneMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);

        $this->timezoneMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Ui\Component\Listing\Columns\Date::class,
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
}
