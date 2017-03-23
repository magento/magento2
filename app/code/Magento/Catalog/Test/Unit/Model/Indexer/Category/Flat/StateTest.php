<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatIndexerMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
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

        $this->indexerRegistryMock = $this->getMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
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
        )->will(
            $this->returnValue(true)
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\State(
            $this->scopeConfigMock,
            $this->indexerRegistryMock
        );
        $this->assertEquals(true, $this->model->isFlatEnabled());
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
        $this->flatIndexerMock->expects($this->any())->method('isValid')->will($this->returnValue($isValid));
        $this->indexerRegistryMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID)
            ->will($this->returnValue($this->flatIndexerMock));

        $this->scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            'catalog/frontend/flat_catalog_category'
        )->will(
            $this->returnValue($isFlatEnabled)
        );

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\State(
            $this->scopeConfigMock,
            $this->indexerRegistryMock,
            $isAvailable
        );
        $this->assertEquals($result, $this->model->isAvailable());
    }

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
