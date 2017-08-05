<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\System\Config;

class ModeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\System\Config\Mode
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
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flatIndexer;

    protected function setUp()
    {
        $this->configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->indexerStateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['loadByIndexer', 'setStatus', 'save', '__wakeup']
        );
        $this->indexerRegistry = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['load', 'setScheduled', 'get']
        );

        $this->flatIndexer = $this->createMock(\Magento\Framework\Indexer\IndexerInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Category\Flat\System\Config\Mode::class,
            [
                'config' => $this->configMock,
                'indexerState' => $this->indexerStateMock,
                'indexerRegistry' => $this->indexerRegistry
            ]
        );
    }

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

        $this->indexerRegistry->expects($this->never())->method('load');
        $this->indexerRegistry->expects($this->never())->method('setScheduled');

        $this->model->processValue();
    }

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
            'catalog_category_flat'
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

        $this->indexerRegistry->expects($this->never())->method('load');
        $this->indexerRegistry->expects($this->never())->method('setScheduled');

        $this->model->processValue();
    }

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

        $this->indexerRegistry->expects($this->once())->method('get')->with('catalog_category_flat')
            ->willReturn($this->flatIndexer);
        $this->flatIndexer->expects($this->once())->method('setScheduled')->with(false);

        $this->model->processValue();
    }
}
