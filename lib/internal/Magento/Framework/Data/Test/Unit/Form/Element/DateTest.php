<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    public function providerGetElementHtmlDateFormat()
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
}
