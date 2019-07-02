<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Date;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Unit tests for \Magento\Ui\Component\Listing\Columns\Date class.
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
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var DataBundle|MockObject
     */
    private $dataBundleMock;

    /**
     * @var Date
     */
    protected $model;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $timezoneMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
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
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->timezoneMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        $this->locale = 'en_US';
        $this->localeResolverMock->expects($this->once())
            ->method('getLocale')
            ->willReturn($this->locale);
        $this->dataBundleMock = $this->createMock(DataBundle::class);

        $this->model = $this->objectManager->getObject(
            Date::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'data' => $this->data,
                'timezone' => $this->timezoneMock,
                'localeResolver' => $this->localeResolverMock,
                'dataBundle' => $this->dataBundleMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testPrepare()
    {
        $dateFormat = 'M/d/Y';
        $mediumDateFormatter = 2;
        $dateTimeFormat = 'MMM d, y h:mm:ss a';

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
        $this->timezoneMock->expects($this->once())
            ->method('getDateTimeFormat')
            ->with($mediumDateFormatter)
            ->willReturn($dateTimeFormat);

        $resourceBundle = new \ResourceBundle('en', 'ICUDATA');
        $this->dataBundleMock->expects($this->once())
            ->method('get')
            ->with($this->locale)
            ->willReturn($resourceBundle);

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
                $this->data['config']['dataType']
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
