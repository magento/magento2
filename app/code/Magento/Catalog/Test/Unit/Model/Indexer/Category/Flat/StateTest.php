<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $flatIndexerMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );

        $this->flatIndexerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );

        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
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

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\State(
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
            ->with(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID)
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

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\State(
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
