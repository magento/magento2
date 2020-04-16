<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model;

/**
 * Class ManagerTest
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Meta | \PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var \Magento\SalesSequence\Model\SequenceFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    private $sequenceFactory;

    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    private $sequenceManager;

    /**
     * @var \Magento\Store\Model\Store | \PHPUnit\Framework\MockObject\MockObject
     */
    private $store;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit\Framework\MockObject\MockObject
     */
    private $meta;

    /**
     * @var \Magento\Framework\DB\Sequence\SequenceInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $sequence;

    /**
     *  Initialization
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sequence = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Sequence\SequenceInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->resourceSequenceMeta = $this->createPartialMock(
            \Magento\SalesSequence\Model\ResourceModel\Meta::class,
            ['loadByEntityTypeAndStore']
        );
        $this->sequenceFactory = $this->createPartialMock(
            \Magento\SalesSequence\Model\SequenceFactory::class,
            ['create']
        );
        $this->meta = $this->createMock(\Magento\SalesSequence\Model\Meta::class);
        $this->store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId']);
        $this->sequenceManager = $helper->getObject(
            \Magento\SalesSequence\Model\Manager::class,
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
