<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Inventory\Model\ReservationBuilder;
use Magento\Inventory\Model\ReservationBuilder\Validator\ReservationBuilderValidatorInterface;
use Magento\Inventory\Model\SnakeToCamelCaseConvertor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;

class ReservationBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var  ObjectManager */
    private $objectManager;

    /** @var ReservationBuilderValidatorInterface */
    private $reservationBuilderValidator;

    /** @var  ReservationInterface */
    private $reservation;

    /** @var  ValidationResult */
    private $validationResult;

    /** @var SnakeToCamelCaseConvertor */
    private $snakeToCamelCaseConvertor;

    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMock();
        $this->reservationBuilderValidator = $this->getMockBuilder(ReservationBuilderValidatorInterface::class)->getMock();
        $this->snakeToCamelCaseConvertor = $this->getMockBuilder(SnakeToCamelCaseConvertor::class)->getMock();
        $this->reservation = $this->getMockBuilder(ReservationInterface::class)->getMock();
        $this->validationResult = $this->getMockBuilder(ValidationResult::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * Return an instance of ReservationBuilder
     *
     * @return ReservationBuilder
     */
    private function createReservationBuilder(): ReservationBuilder
    {
        return (new ObjectManager($this))->getObject(
            ReservationBuilder::class,
            [
                'objectManager' => $this->objectManager,
                'reservationBuilderValidator' => $this->reservationBuilderValidator,
                'snakeToCamelCaseConvertor' => $this->snakeToCamelCaseConvertor,
            ]
        );
    }

    /**
     * @dataProvider getValidReservationData
     */
    public function testBuildValidReservation($reservationData, $reservationMappedData)
    {
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

        $this->validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->reservationBuilderValidator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($this->validationResult);

        /** @var ReservationBuilderInterface $reservationBuilder */
        $reservationBuilder = $this->createReservationBuilder();
        $reservationBuilder->setStockId($reservationData[ReservationInterface::STOCK_ID]);
        $reservationBuilder->setSku($reservationData[ReservationInterface::SKU]);
        $reservationBuilder->setQuantity($reservationData[ReservationInterface::QUANTITY]);
        $reservationBuilder->setMetadata($reservationData[ReservationInterface::METADATA]);

        $this->assertSame($this->reservation, $reservationBuilder->build());
    }

    public function getValidReservationData()
    {
        return [
            [
                [
                    ReservationInterface::STOCK_ID => 1,
                    ReservationInterface::SKU => 'somesku',
                    ReservationInterface::QUANTITY => 11,
                    ReservationInterface::METADATA => 'some meta data',
                ],
                [
                    'stockId' => 1,
                    'sku' => 'somesku',
                    'quantity' => 11,
                    'metadata' => 'some meta data',
                ],
            ],
        ];
    }
}
