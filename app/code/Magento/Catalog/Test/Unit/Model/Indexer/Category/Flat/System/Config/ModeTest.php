<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\System\Config;

use Magento\Catalog\Model\Indexer\Category\Flat\System\Config\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
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
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistry;

    /**
     * @var IndexerInterface|MockObject
     */
    protected $flatIndexer;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->indexerStateMock = $this->createPartialMock(
            State::class,
            ['loadByIndexer', 'setStatus', 'save']
        );
        $this->indexerRegistry = $this->getMockBuilder(IndexerRegistry::class)
            ->addMethods(['load', 'setScheduled'])
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->flatIndexer = $this->getMockForAbstractClass(IndexerInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Mode::class,
            [
                'config' => $this->configMock,
                'indexerState' => $this->indexerStateMock,
                'indexerRegistry' => $this->indexerRegistry
            ]
        );
    }

    /**
     * @return array
     */
    public static function dataProviderProcessValueEqual()
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

        $this->indexerRegistry->expects($this->never())->method('load');
        $this->indexerRegistry->expects($this->never())->method('setScheduled');

        $this->model->processValue();
    }

    /**
     * @return array
     */
    public static function dataProviderProcessValueOn()
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
            'catalog_category_flat'
        )->willReturnSelf();
        $this->indexerStateMock->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            'invalid'
        )->willReturnSelf();
        $this->indexerStateMock->expects($this->once())->method('save')->willReturnSelf();

        $this->indexerRegistry->expects($this->never())->method('load');
        $this->indexerRegistry->expects($this->never())->method('setScheduled');

        $this->model->processValue();
    }

    /**
     * @return array
     */
    public static function dataProviderProcessValueOff()
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

        $this->indexerRegistry->expects($this->once())->method('get')->with('catalog_category_flat')
            ->willReturn($this->flatIndexer);
        $this->flatIndexer->expects($this->once())->method('setScheduled')->with(false);

        $this->model->processValue();
    }
}
