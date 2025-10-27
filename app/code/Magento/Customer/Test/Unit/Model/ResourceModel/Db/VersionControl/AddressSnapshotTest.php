<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Db\VersionControl;

use Magento\Customer\Model\ResourceModel\Db\VersionControl\AddressSnapshot;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressSnapshotTest extends TestCase
{
    /**
     * @var AddressSnapshot
     */
    private AddressSnapshot $model;

    /**
     * @var Metadata|MockObject
     */
    private Metadata $metadataMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private SerializerInterface $serializer;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->metadataMock = $this->createMock(Metadata::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $this->model = new AddressSnapshot(
            $this->metadataMock,
            $this->serializer
        );
    }

    /**
     * @param bool $isCustomerSaveTransaction
     * @param int $isDefaultBilling
     * @param int $isDefaultShipping
     * @param bool $expected
     * @dataProvider dataProviderIsModified
     * @throws LocalizedException
     */
    public function testIsModified($isCustomerSaveTransaction, $isDefaultBilling, $isDefaultShipping, $expected): void
    {
        $entityId = 1;

        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getId',
                'getIsDefaultBilling',
                'getIsDefaultShipping',
                'getIsCustomerSaveTransaction',
            ])
            ->onlyMethods(['getData', 'getDataByKey'])
            ->getMock();

        $dataObjectMock->expects($this->any())
            ->method('getId')
            ->willReturn($entityId);
        $dataObjectMock->expects($this->once())
            ->method('getData')
            ->willReturn(['is_billing_address' => 1]);
        $dataObjectMock->expects($this->once())
            ->method('getDataByKey')
            ->with('is_billing_address')
            ->willReturn(1);
        $dataObjectMock->expects($this->once())
            ->method('getIsCustomerSaveTransaction')
            ->willReturn($isCustomerSaveTransaction);
        $dataObjectMock->expects($this->any())
            ->method('getIsDefaultBilling')
            ->willReturn($isDefaultBilling);
        $dataObjectMock->expects($this->any())
            ->method('getIsDefaultShipping')
            ->willReturn($isDefaultShipping);

        $this->metadataMock->expects($this->once())
            ->method('getFields')
            ->with($dataObjectMock)
            ->willReturn(['is_billing_address' => null]);

        $this->model->registerSnapshot($dataObjectMock);

        $this->assertEquals($expected, $this->model->isModified($dataObjectMock));
    }

    /**
     * @return array
     */
    public static function dataProviderIsModified(): array
    {
        return [
            [false, 1, 1, true],
            [true, 0, 0, false],
            [false, 1, 0, true],
            [false, 0, 1, true],
        ];
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testIsModifiedBypass(): void
    {
        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->onlyMethods(['getData'])
            ->getMock();

        $dataObjectMock->expects($this->any())
            ->method('getId')
            ->willReturn(null);
        $dataObjectMock->expects($this->once())
            ->method('getData')
            ->willReturn(['is_billing_address' => 1]);

        $this->metadataMock->expects($this->once())
            ->method('getFields')
            ->with($dataObjectMock)
            ->willReturn(['is_billing_address' => null]);

        $this->model->registerSnapshot($dataObjectMock);

        $this->assertTrue($this->model->isModified($dataObjectMock));
    }
}
