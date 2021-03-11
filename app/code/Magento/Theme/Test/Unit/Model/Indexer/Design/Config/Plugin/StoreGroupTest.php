<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Theme\Model\Data\Design\Config;
use Magento\Theme\Model\Indexer\Design\Config\Plugin\StoreGroup;

class StoreGroupTest extends \PHPUnit\Framework\TestCase
{
    /** @var StoreGroup */
    protected $model;

    /** @var IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->indexerRegistryMock = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new StoreGroup($this->indexerRegistryMock);
    }

    public function testAfterDelete()
    {
        /** @var \Magento\Store\Model\Group|\PHPUnit\Framework\MockObject\MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder(\Magento\Store\Model\Group::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var IndexerInterface|\PHPUnit\Framework\MockObject\MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder(\Magento\Framework\Indexer\IndexerInterface::class)
            ->getMockForAbstractClass();
        $indexerMock->expects($this->once())
            ->method('invalidate');

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($indexerMock);

        $this->assertEquals($subjectMock, $this->model->afterDelete($subjectMock, $subjectMock));
    }
}
