<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model\Reservation;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validation\ValidationResult;
use Magento\Inventory\Model\Reservation\ReservationBuilder;
use Magento\Inventory\Model\Reservation\Validator\ReservationValidatorInterface;
use Magento\Inventory\Model\SnakeToCamelCaseConvertor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use PHPUnit\Framework\TestCase;

class ReservationBuilderTest extends TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var ReservationValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reservationValidator;

    /**
     * @var ReservationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reservation;

    /**
     * @var ValidationResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResult;

    /**
     * @var SnakeToCamelCaseConvertor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $snakeToCamelCaseConvertor;

    /**
     * @var ReservationBuilder
     */
    private $reservationBuilder;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMock();
        $this->reservationValidator = $this->getMockBuilder(ReservationValidatorInterface::class)->getMock();
        $this->snakeToCamelCaseConvertor = $this->getMockBuilder(SnakeToCamelCaseConvertor::class)->getMock();
        $this->reservation = $this->getMockBuilder(ReservationInterface::class)->getMock();
        $this->validationResult = $this->getMockBuilder(ValidationResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reservationBuilder = (new ObjectManager($this))->getObject(
            ReservationBuilder::class,
            [
                'objectManager' => $this->objectManager,
                'reservationValidator' => $this->reservationValidator,
                'snakeToCamelCaseConvertor' => $this->snakeToCamelCaseConvertor,
            ]
        );
    }

    public function testBuild()
    {
        $reservationData = [
            ReservationInterface::STOCK_ID => 1,
            ReservationInterface::SKU => 'somesku',
            ReservationInterface::QUANTITY => 11,
            ReservationInterface::METADATA => 'some meta data',
        ];

        $reservationMappedData = [
            'stockId' => 1,
            'sku' => 'somesku',
            'quantity' => 11,
            'metadata' => 'some meta data',
        ];

        $this->snakeToCamelCaseConvertor
            ->expects($this->once())
            ->method('convert')
            ->with(array_keys($reservationData))
            ->willReturn(array_keys($reservationMappedData));

        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with(ReservationInterface::class, $reservationMappedData)
            ->willReturn($this->reservation);

        $this->reservationValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->reservation)
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
}
