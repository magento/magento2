<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Date
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Date;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    /**
     * @var Date
     */
    protected $model;

    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDateMock;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(Factory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->model = new Date(
            $this->factoryMock,
            $this->collectionFactoryMock,
            $this->escaperMock,
            $this->localeDateMock
        );
    }

    public function testGetElementHtmlException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Output format is not specified. Please specify "format" key in constructor, or set it using setFormat().'
        );
        $formMock = $this->getFormMock('never');
        $this->model->setForm($formMock);
        $this->model->getElementHtml();
    }

    /**
     * @param $fieldName
     * @dataProvider providerGetElementHtmlDateFormat
     */
    public function testGetElementHtmlDateFormat($fieldName)
    {
        $formMock = $this->getFormMock('once');
        $this->model->setForm($formMock);

        $this->model->setData(
            [
                $fieldName => 'yyyy-MM-dd',
                'name' => 'test_name',
                'html_id' => 'test_name',
            ]
        );
        $this->model->getElementHtml();
    }

    /**
     * @return array
     */
    public static function providerGetElementHtmlDateFormat()
    {
        return [
            ['date_format'],
            ['format'],
        ];
    }

    /**
     * @param $exactly
     * @return MockObject
     */
    protected function getFormMock($exactly)
    {
        $formMock = $this->getMockBuilder(\stdClass::class)->addMethods(
            ['getFieldNameSuffix', 'getHtmlIdPrefix', 'getHtmlIdSuffix']
        )
            ->disableOriginalConstructor()
            ->getMock();
        foreach (['getFieldNameSuffix', 'getHtmlIdPrefix', 'getHtmlIdSuffix'] as $method) {
            switch ($exactly) {
                case 'once':
                    $count = $this->once();
                    break;
                case 'never':
                default:
                    $count = $this->never();
            }
            $formMock->expects($count)->method($method);
        }

        return $formMock;
    }

    /**
     * @dataProvider providerGetValue
     * @param string|null $dateFormat
     * @param string|null $format
     * @param string|null $timeFormat
     * @param string $expectedFormat
     */
    public function testGetValue(?string $dateFormat, ?string $format, ?string $timeFormat, string $expectedFormat)
    {
        $dateTime = new \DateTime('2025-10-13 10:36:00', new \DateTimeZone('America/Los_Angeles'));
        $this->model->setValue($dateTime);
        $this->model->setDateFormat($dateFormat);
        $this->model->setFormat($format);
        $this->model->setTimeFormat($timeFormat);

        $this->localeDateMock->expects($this->once())
            ->method('formatDateTime')
            ->with(
                $dateTime,
                null,
                null,
                null,
                $this->equalTo($dateTime->getTimezone()),
                $this->equalTo($expectedFormat)
            )
            ->willReturn('2025-10-13 10:36:00');

        $this->model->getValue();
    }

    public function providerGetValue()
    {
        return [
            [null, 'yyyy-mm-dd', 'hh:mm:ss', 'yyyy-mm-dd hh:mm:ss'],
            [null, 'yy-mm-dd', null, 'yy-mm-dd'],
            ['yyyy-mm-dd', null, null, 'yyyy-mm-dd'],
            ['yyyy-mm-dd', 'yy-mm-dd', 'hh:mm:ss', 'yyyy-mm-dd hh:mm:ss'],
            ['yyyy-mm-dd', 'yy-mm-dd', null, 'yyyy-mm-dd']
        ];
    }
}
