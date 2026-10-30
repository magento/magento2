<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Sales\Ui\Component\Listing\Column\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{

    /**
     * @var Address
     */
    protected $model;

    /** @var CountryInformationAcquirerInterface|MockObject */
    private $countryInfo;

    /**
     * @var MockObject|Escaper
     */
    protected $escaper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMock();
        $processor = $this->createMock(Processor::class);
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $this->countryInfo = $this->createMock(CountryInformationAcquirerInterface::class);
        $this->model = $objectManager->getObject(
            Address::class,
            [
                'context' => $contextMock,
                'escaper' => $this->escaper,
                'countryInfo' => $this->countryInfo,
            ]
        );
    }

    public function testPrepareDataSource()
    {
        $itemName = 'itemName';
        $oldItemValue = "itemValue\n";
        $newItemValue = "itemValue<br />\n";
        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => $oldItemValue]
                ]
            ]
        ];

        $this->model->setData('name', $itemName);
        $this->escaper->expects($this->any())->method('escapeHtml')->with($oldItemValue)->willReturnArgument(0);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }

    public function testPrepareDataSourceKeepsCountryCodeWhenCountryIsUnknown(): void
    {
        $itemName = 'shipping_address';
        $oldItemValue = 'a,b,XX';
        $expectedValue = 'a, b, XX';

        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => $oldItemValue],
                ],
            ],
        ];

        $this->countryInfo->expects($this->once())
            ->method('getCountryInfo')
            ->with('XX')
            ->willThrowException(new NoSuchEntityException(__('No such entity')));

        $this->model->setData('name', $itemName);

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with($expectedValue)
            ->willReturnArgument(0);

        $result = $this->model->prepareDataSource($dataSource);

        $this->assertSame($expectedValue, $result['data']['items'][0][$itemName]);
    }

    public function testPrepareDataSourceCachesCountryNameLookup(): void
    {
        $itemName = 'billing_address';
        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => 'a,b,IN'],
                    [$itemName => 'x,y,IN'],
                ],
            ],
        ];

        $countryInformation = $this->createMock(CountryInformationInterface::class);
        $countryInformation->expects($this->once())->method('getFullNameLocale')->willReturn('India');

        // Cache should ensure only one lookup for IN across both rows.
        $this->countryInfo->expects($this->once())
            ->method('getCountryInfo')
            ->with('IN')
            ->willReturn($countryInformation);

        $this->model->setData('name', $itemName);

        $this->escaper->expects($this->exactly(2))
            ->method('escapeHtml')
            ->willReturnCallback(function (string $value) {
                static $i = 0;
                $expected = ['a, b, India', 'x, y, India'];

                $this->assertSame($expected[$i], $value);
                $i++;

                return $value;
            });

        $result = $this->model->prepareDataSource($dataSource);

        $this->assertSame('a, b, India', $result['data']['items'][0][$itemName]);
        $this->assertSame('x, y, India', $result['data']['items'][1][$itemName]);
    }
}
