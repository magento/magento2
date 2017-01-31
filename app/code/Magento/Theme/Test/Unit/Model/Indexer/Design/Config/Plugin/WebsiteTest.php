<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Indexer\Design\Config\Plugin;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Theme\Model\Data\Design\Config;
use Magento\Theme\Model\Indexer\Design\Config\Plugin\Website;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Website */
    protected $model;

    /** @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->indexerRegistryMock = $this->getMockBuilder('Magento\Framework\Indexer\IndexerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Website($this->indexerRegistryMock);
    }

    public function testAroundSave()
    {
        $subjectId = 0;

        /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('getId')
            ->willReturn($subjectId);

        $closureMock = function () use ($subjectMock) {
            return $subjectMock;
        };

        /** @var IndexerInterface|\PHPUnit_Framework_MockObject_MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder('Magento\Framework\Indexer\IndexerInterface')
            ->getMockForAbstractClass();
        $indexerMock->expects($this->once())
            ->method('invalidate');

        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Config::DESIGN_CONFIG_GRID_INDEXER_ID)
            ->willReturn($indexerMock);

        $this->assertEquals($subjectMock, $this->model->aroundSave($subjectMock, $closureMock));
    }

    public function testAroundSaveWithExistentSubject()
    {
        $subjectId = 1;

        /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->once())
            ->method('getId')
            ->willReturn($subjectId);

        $closureMock = function () use ($subjectMock) {
            return $subjectMock;
        };

        $this->indexerRegistryMock->expects($this->never())
            ->method('get');

        $this->assertEquals($subjectMock, $this->model->aroundSave($subjectMock, $closureMock));
    }

    public function testAfterDelete()
    {
        /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockBuilder('Magento\Store\Model\Website')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var IndexerInterface|\PHPUnit_Framework_MockObject_MockObject $indexerMock */
        $indexerMock = $this->getMockBuilder('Magento\Framework\Indexer\IndexerInterface')
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
