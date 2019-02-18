<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DateTest
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    const TEST_TIME = '2000-04-12 16:34:12';

    private $data = [
        'js_config' => [
            'extends' => 'test_config_extends',
        ],
        'config' => [
            'dataType' => 'testType',
        ],
        'name' => 'field_name',
    ];

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * @var \Magento\Ui\Component\Listing\Columns\Date
     */
    protected $model;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|MockObject
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
        $this->uiComponentFactoryMock = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->timezoneMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Ui\Component\Listing\Columns\Date::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'data' => $this->data,
                'timezone' => $this->timezoneMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testPrepare()
    {
        $dateFormat = 'M/d/Y';

        $this->data['config']['filter'] = [
            'filterType' => 'dateRange',
            'templates' => [
                'date' => [
                    'options' => [
                        'dateFormat' => $dateFormat,
                    ],
                ],
            ],
        ];

        $this->timezoneMock->expects($this->once())
            ->method('getDateFormatWithLongYear')
            ->willReturn($dateFormat);

        /** @var \Magento\Framework\View\Element\UiComponent\Processor|MockObject $processor */
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);

        /** @var \Magento\Framework\View\Element\UiComponentInterface|MockObject $wrappedComponentMock */
        $wrappedComponentMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false
        );

        $wrappedComponentMock->expects($this->once())
            ->method('getContext')
            ->willReturn($this->contextMock);

        $this->uiComponentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->data['name'],
                $this->data['config']['dataType'],
                array_merge(['context' => $this->contextMock], $this->data)
            )
            ->willReturn($wrappedComponentMock);

        $this->model->prepare();
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
