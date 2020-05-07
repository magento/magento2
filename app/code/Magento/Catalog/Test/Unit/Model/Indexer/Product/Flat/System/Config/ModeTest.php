<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\System\Config;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\Indexer\Product\Flat\System\Config\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModeTest extends TestCase
{
    /**
     * @var Mode
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var State|MockObject
     */
    protected $indexerStateMock;

    /**
     * @var Processor|MockObject
     */
    protected $indexerProcessorMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->indexerStateMock = $this->createPartialMock(
            State::class,
            ['loadByIndexer', 'setStatus', 'save']
        );
        $this->indexerProcessorMock = $this->createPartialMock(
            Processor::class,
            ['getIndexer']
        );

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Mode::class,
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
        )->willReturn(
            $oldValue
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
        )->willReturn(
            $oldValue
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'loadByIndexer'
        )->with(
            'catalog_product_flat'
        )->willReturnSelf();
        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            'invalid'
        )->willReturnSelf();
        $this->indexerStateMock->expects($this->once())->method('save')->willReturnSelf();

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
        )->willReturn(
            $oldValue
        );

        $this->model->setValue($value);

        $this->indexerStateMock->expects($this->never())->method('loadByIndexer');
        $this->indexerStateMock->expects($this->never())->method('setStatus');
        $this->indexerStateMock->expects($this->never())->method('save');

        $indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setScheduled']
        );
        $indexerMock->expects($this->once())->method('setScheduled')->with(false);

        $this->indexerProcessorMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->willReturn(
            $indexerMock
        );

        $this->model->processValue();
    }
}
