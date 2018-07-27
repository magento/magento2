<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\System\Config;

class ModeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\System\Config\Mode
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerStateMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerProcessorMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->indexerStateMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State',
            ['loadByIndexer', 'setStatus', 'save', '__wakeup'],
            [],
            '',
            false
        );
        $this->indexerProcessorMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor',
            ['getIndexer'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Product\Flat\System\Config\Mode',
            [
                'config' => $this->configMock,
                'indexerState' => $this->indexerStateMock,
                'productFlatIndexerProcessor' => $this->indexerProcessorMock
            ]
        );
    }

    /**
     * @return array
     */
    public function dataProviderProcessValueEqual()
    {
        return [['0', '0'], ['', '0'], ['0', ''], ['1', '1']];
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @dataProvider dataProviderProcessValueEqual
     */
    public function testProcessValueEqual($oldValue, $value)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects($this->never())->method('loadByIndexer');
        $this->indexerStateMock->expects($this->never())->method('setStatus');
        $this->indexerStateMock->expects($this->never())->method('save');

        $this->indexerProcessorMock->expects($this->never())->method('getIndexer');

        $this->model->processValue();
    }

    /**
     * @return array
     */
    public function dataProviderProcessValueOn()
    {
        return [['0', '1'], ['', '1']];
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @dataProvider dataProviderProcessValueOn
     */
    public function testProcessValueOn($oldValue, $value)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'loadByIndexer'
        )->with(
            'catalog_product_flat'
        )->will(
            $this->returnSelf()
        );
        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            'invalid'
        )->will(
            $this->returnSelf()
        );
        $this->indexerStateMock->expects($this->once())->method('save')->will($this->returnSelf());

        $this->indexerProcessorMock->expects($this->never())->method('getIndexer');

        $this->model->processValue();
    }

    /**
     * @return array
     */
    public function dataProviderProcessValueOff()
    {
        return [['1', '0'], ['1', '']];
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @dataProvider dataProviderProcessValueOff
     */
    public function testProcessValueOff($oldValue, $value)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects($this->never())->method('loadByIndexer');
        $this->indexerStateMock->expects($this->never())->method('setStatus');
        $this->indexerStateMock->expects($this->never())->method('save');

        $indexerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['setScheduled', '__wakeup']
        );
        $indexerMock->expects($this->once())->method('setScheduled')->with(false);

        $this->indexerProcessorMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->will(
            $this->returnValue($indexerMock)
        );

        $this->model->processValue();
    }
}
