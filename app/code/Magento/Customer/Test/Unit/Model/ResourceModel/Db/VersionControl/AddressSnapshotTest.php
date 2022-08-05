<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel\Db\VersionControl;

use Magento\Customer\Model\ResourceModel\Db\VersionControl\AddressSnapshot;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressSnapshotTest extends TestCase
{
    /**
     * @var AddressSnapshot
     */
    private $model;

    /**
     * @var Metadata|MockObject
     */
    private $metadataMock;

    protected function setUp(): void
    {
        $this->metadataMock = $this->getMockBuilder(
            Metadata::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->model = new AddressSnapshot(
            $this->metadataMock
        );
    }

    /**
     * @param bool $isCustomerSaveTransaction
     * @param int $isDefaultBilling
     * @param int $isDefaultShipping
     * @param bool $expected
     * @dataProvider dataProviderIsModified
     */
    public function testIsModified(
        $isCustomerSaveTransaction,
        $isDefaultBilling,
        $isDefaultShipping,
        $expected
    ) {
        $entityId = 1;

        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getData',
                'getDataByKey',
                'getIsDefaultBilling',
                'getIsDefaultShipping',
                'getIsCustomerSaveTransaction',
            ])
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
    public function dataProviderIsModified()
    {
        return [
            [false, 1, 1, true],
            [true, 0, 0, false],
            [false, 1, 0, true],
            [false, 0, 1, true],
        ];
    }

    public function testIsModifiedBypass()
    {
        $dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getData',
            ])
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
