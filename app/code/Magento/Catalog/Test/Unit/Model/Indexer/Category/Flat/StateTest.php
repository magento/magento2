<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat;

use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var IndexerInterface|MockObject
     */
    protected $flatIndexerMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            ScopeConfigInterface::class
        );

        $this->flatIndexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState']
        );

        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
    }

    public function testIsFlatEnabled()
    {
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            'catalog/frontend/flat_catalog_category'
        )->willReturn(
            true
        );

        $this->model = new State(
            $this->scopeConfigMock,
            $this->indexerRegistryMock
        );
        $this->assertTrue($this->model->isFlatEnabled());
    }

    /**
     * @param $isAvailable
     * @param $isFlatEnabled
     * @param $isValid
     * @param $result
     * @dataProvider isAvailableDataProvider
     */
    public function testIsAvailable($isAvailable, $isFlatEnabled, $isValid, $result)
    {
        $this->flatIndexerMock->expects($this->any())->method('load')->with('catalog_category_flat');
        $this->flatIndexerMock->expects($this->any())->method('isValid')->willReturn($isValid);
        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(State::INDEXER_ID)
            ->willReturn($this->flatIndexerMock);

        $this->scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            'catalog/frontend/flat_catalog_category'
        )->willReturn(
            $isFlatEnabled
        );

        $this->model = new State(
            $this->scopeConfigMock,
            $this->indexerRegistryMock,
            $isAvailable
        );
        $this->assertEquals($result, $this->model->isAvailable());
    }

    /**
     * @return array
     */
    public function isAvailableDataProvider()
    {
        return [
            [false, true, true, false],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, true]
        ];
    }
}
