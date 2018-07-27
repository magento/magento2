<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model;

/**
 * Class ManagerTest
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var \Magento\SalesSequence\Model\SequenceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceFactory;

    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    private $sequenceManager;

    /**
     * @var \Magento\Store\Model\Store | \PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit_Framework_MockObject_MockObject
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
            'Magento\SalesSequence\Model\ResourceModel\Meta',
            ['loadByEntityTypeAndStore'],
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
            'Magento\SalesSequence\Model\Meta',
            [],
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
            'Magento\SalesSequence\Model\Manager',
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
        $this->resourceSequenceMeta->expects($this->once())
            ->method('loadByEntityTypeAndStore')
            ->with($entityType, $storeId)
            ->willReturn($this->meta);
        $this->sequenceFactory->expects($this->once())->method('create')->with([
            'meta' => $this->meta
        ])->willReturn($this->sequence);
        $this->assertSame($this->sequence, $this->sequenceManager->getSequence($entityType, $storeId));
    }
}
