<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model\Resource\Sequence;

/**
 * Class SequenceManagerTest
 */
class SequenceManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesSequence\Model\Resource\Sequence\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var \Magento\SalesSequence\Model\SequenceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceFactory;

    /**
     * @var \Magento\SalesSequence\Model\Sequence\SequenceManager
     */
    private $sequenceManager;

    /**
     * @var \Magento\Store\Model\Store | \PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\Sales\Model\Order | \PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Magento\SalesSequence\Model\Sequence\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $meta;

    /**
     * @var \Magento\Framework\DB\Sequence\SequenceInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sequence;

    /**
     *  Initialization
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sequence = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Sequence\SequenceInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->resourceSequenceMeta = $this->getMock(
            'Magento\SalesSequence\Model\Resource\Sequence\Meta',
            ['loadBy'],
            [],
            '',
            false
        );
        $this->sequenceFactory = $this->getMock(
            'Magento\SalesSequence\Model\SequenceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->meta = $this->getMock(
            'Magento\SalesSequence\Model\Sequence\Meta',
            [],
            [],
            '',
            false
        );
        $this->order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getEntityType', 'getStore'],
            [],
            '',
            false
        );
        $this->store = $this->getMock(
            'Magento\Store\Model\Store',
            ['getId'],
            [],
            '',
            false
        );
        $this->sequenceManager = $helper->getObject(
            'Magento\SalesSequence\Model\Sequence\SequenceManager',
            [
                'resourceSequenceMeta' => $this->resourceSequenceMeta,
                'sequenceFactory' => $this->sequenceFactory
            ]
        );
    }

    public function testGetSequence()
    {
        $entityType = 'order';
        $storeId = 1;
        $this->order->expects($this->once())->method('getEntityType')->willReturn($entityType);
        $this->order->expects($this->once())->method('getStore')->willReturn($this->store);
        $this->store->expects($this->once())->method('getId')->willReturn($storeId);
        $this->resourceSequenceMeta->expects($this->once())
            ->method('loadBy')
            ->with($entityType, $storeId)
            ->willReturn($this->meta);
        $this->sequenceFactory->expects($this->once())->method('create')->with([
            'meta' => $this->meta
        ])->willReturn($this->sequence);
        $this->assertSame($this->sequence, $this->sequenceManager->getSequence($this->order));
    }
}
