<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Test\Unit\Model;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesSequence\Model\Manager;
use Magento\SalesSequence\Model\ResourceModel\Meta;
use Magento\SalesSequence\Model\SequenceFactory;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * @var Meta|MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var SequenceFactory|MockObject
     */
    private $sequenceFactory;

    /**
     * @var Manager
     */
    private $sequenceManager;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var \Magento\SalesSequence\Model\Meta|MockObject
     */
    private $meta;

    /**
     * @var SequenceInterface|MockObject
     */
    private $sequence;

    /**
     *  Initialization
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->sequence = $this->getMockForAbstractClass(
            SequenceInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->resourceSequenceMeta = $this->createPartialMock(
            Meta::class,
            ['loadByEntityTypeAndStore']
        );
        $this->sequenceFactory = $this->createPartialMock(
            SequenceFactory::class,
            ['create']
        );
        $this->meta = $this->createMock(\Magento\SalesSequence\Model\Meta::class);
        $this->store = $this->createPartialMock(Store::class, ['getId']);
        $this->sequenceManager = $helper->getObject(
            Manager::class,
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
