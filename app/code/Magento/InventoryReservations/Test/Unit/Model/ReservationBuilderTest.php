<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryReservations\Model\ReservationBuilder;
use Magento\InventoryReservations\Model\SnakeToCamelCaseConverter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use PHPUnit\Framework\TestCase;

class ReservationBuilderTest extends TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var ReservationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reservation;

    /**
     * @var ValidationResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResult;

    /**
     * @var SnakeToCamelCaseConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snakeToCamelCaseConverter;

    /** @var  ValidationResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $validationResultFactory;

    /**
     * @var ReservationBuilder
     */
    private $reservationBuilder;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMock();
        $this->snakeToCamelCaseConverter = $this->getMockBuilder(SnakeToCamelCaseConverter::class)->getMock();
        $this->reservation = $this->getMockBuilder(ReservationInterface::class)->getMock();
        $this->validationResult = $this->getMockBuilder(ValidationResult::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationResultFactory = $this->getMockBuilder(ValidationResultFactory::class)->getMock();

        $this->reservationBuilder = (new ObjectManager($this))->getObject(
            ReservationBuilder::class,
            [
                'objectManager' => $this->objectManager,
                'snakeToCamelCaseConverter' => $this->snakeToCamelCaseConverter,
                'validationResultFactory' => $this->validationResultFactory,
            ]
        );
    }

    public function testBuild()
    {
        $reservationData = [
            ReservationInterface::RESERVATION_ID => null,
            ReservationInterface::STOCK_ID => 1,
            ReservationInterface::SKU => 'somesku',
            ReservationInterface::QUANTITY => 11,
            ReservationInterface::METADATA => 'some meta data',
        ];

        $reservationMappedData = [
            'reservationId' => null,
            'stockId' => 1,
            'sku' => 'somesku',
            'quantity' => 11,
            'metadata' => 'some meta data',
        ];

        $this->snakeToCamelCaseConverter
            ->expects($this->once())
            ->method('convert')
            ->with(array_keys($reservationData))
            ->willReturn(array_keys($reservationMappedData));

        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with(ReservationInterface::class, $reservationMappedData)
            ->willReturn($this->reservation);

        $this->validationResultFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->validationResult);

        $this->validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->reservationBuilder->setStockId($reservationData[ReservationInterface::STOCK_ID]);
        $this->reservationBuilder->setSku($reservationData[ReservationInterface::SKU]);
        $this->reservationBuilder->setQuantity($reservationData[ReservationInterface::QUANTITY]);
        $this->reservationBuilder->setMetadata($reservationData[ReservationInterface::METADATA]);

        self::assertEquals($this->reservation, $this->reservationBuilder->build());
    }

    /**
     * @param array $firstSetter
     * @param array $secondSetter
     * @dataProvider getSettersAndValues
     * @expectedException \Magento\Framework\Validation\ValidationException
     * @expectedExceptionMessage  Validation error
     */
    public function testThrowValidationException(array $firstSetter, array $secondSetter)
    {
        $this->validationResultFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->validationResult);

        $this->validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $method = $firstSetter['method'];
        $argument = $firstSetter['argument'];
        $this->reservationBuilder->$method($argument);

        $method = $secondSetter['method'];
        $argument = $secondSetter['argument'];
        $this->reservationBuilder->$method($argument);

        $this->reservationBuilder->build();
    }

    /**
     * @return array
     */
    public function getSettersAndValues(): array
    {
        return [
            'with_missing_stock_id' => [
                ['method' => 'setSku', 'argument' => 'somesku'],
                ['method' => 'setQuantity', 'argument' => 11]
            ],
            'with_missing_sku' => [
                ['method' => 'setStockId', 'argument' => 1],
                ['method' => 'setQuantity', 'argument' => 11],
            ],
            'with_missing_qty' => [
                ['method' => 'setStockId', 'argument' => 1],
                ['method' => 'setSku', 'argument' => 'somesku'],
            ],
        ];
    }
}
