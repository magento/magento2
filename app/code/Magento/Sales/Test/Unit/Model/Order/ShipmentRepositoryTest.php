<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for shipment repository class.
 */
class ShipmentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $subject;

    /**
     * Sales resource metadata.
     *
     * @var \Magento\Sales\Model\Resource\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->metadata = $this->getMock(
            'Magento\Sales\Model\Resource\Metadata',
            ['getNewInstance', 'getMapper'],
            [],
            '',
            false
        );

        $this->subject = $objectManager->getObject(
            'Magento\Sales\Model\Order\ShipmentRepository',
            [
                'metadata' => $this->metadata
            ]
        );
    }

    /**
     * @param int|null $id
     * @param int|null $entityId
     * @dataProvider getDataProvider
     */
    public function testGet($id, $entityId)
    {
        if (!$id) {
            $this->setExpectedException(
                'Magento\Framework\Exception\InputException'
            );

            $this->subject->get($id);
        } else {
            $shipment = $this->getMock(
                'Magento\Sales\Model\Order\Shipment',
                ['getEntityId'],
                [],
                '',
                false
            );
            $shipment->expects($this->once())
                ->method('getEntityId')
                ->willReturn($entityId);

            $mapper = $this->getMockForAbstractClass(
                'Magento\Framework\Model\Resource\Db\AbstractDb',
                [],
                '',
                false,
                true,
                true,
                ['load']
            );
            $mapper->expects($this->once())
                ->method('load')
                ->with($shipment, $id)
                ->willReturnSelf();

            $this->metadata->expects($this->once())
                ->method('getNewInstance')
                ->willReturn($shipment);
            $this->metadata->expects($this->once())
                ->method('getMapper')
                ->willReturn($mapper);

            if (!$entityId) {
                $this->setExpectedException(
                    'Magento\Framework\Exception\NoSuchEntityException'
                );

                $this->subject->get($id);
            } else {
                $this->assertEquals($shipment, $this->subject->get($id));

                $mapper->expects($this->never())
                    ->method('load');

                $this->metadata->expects($this->never())
                    ->method('getNewInstance');
                $this->metadata->expects($this->never())
                    ->method('getMapper');

                $this->assertEquals($shipment, $this->subject->get($id));
            }
        }
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [null, null],
            [1, null],
            [1, 1]
        ];
    }
}
