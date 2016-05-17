<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel\Db\VersionControl;

use Magento\Customer\Model\ResourceModel\Db\VersionControl\AddressSnapshot;

class AddressSnapshotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressSnapshot
     */
    private $model;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataMock;

    protected function setUp()
    {
        $this->metadataMock = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata')
            ->disableOriginalConstructor()
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

        $dataObjectMock = $this->getMockBuilder('Magento\Framework\DataObject')
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
        $dataObjectMock = $this->getMockBuilder('Magento\Framework\DataObject')
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

        $this->assertEquals(true, $this->model->isModified($dataObjectMock));
    }
}
