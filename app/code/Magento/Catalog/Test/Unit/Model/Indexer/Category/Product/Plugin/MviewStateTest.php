<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Framework\Mview\View\ChangelogInterface;
use Magento\Framework\Mview\View\StateInterface;
use PHPUnit\Framework\TestCase;

class MviewStateTest extends TestCase
{
    /**
     * @return array
     */
    public static function afterSetStatusSuspendDataProvider()
    {
        return [['suspended', 'idle'], ['suspended', 'working']];
    }

    /**
     * @param string $stateStatus
     * @param string $relatedStatus
     */
    #[DataProvider('afterSetStatusSuspendDataProvider')]
    public function testAfterSetStatusSuspend($stateStatus, $relatedStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;
        $relatedVersion = 'related_version';

        $state = $this->createMock(StateInterface::class);

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->createMock(StateInterface::class);

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('enabled');

        $relatedViewState->expects($this->once())->method('getStatus')->willReturn($relatedStatus);

        $relatedViewState->expects($this->once())->method('save')->willReturnSelf();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setVersionId'
        )->with(
            $relatedVersion
        )->willReturnSelf(
        );

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $stateStatus
        )->willReturnSelf(
        );

        $relatedViewChangelog = $this->createMock(ChangelogInterface::class);

        $relatedViewChangelog->expects(
            $this->once()
        )->method(
            'setViewId'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewChangelog->expects($this->once())->method('getVersion')->willReturn($relatedVersion);

        $model = new MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public static function afterSetStatusResumeDataProvider()
    {
        return [['idle', 'suspended'], ['working', 'suspended']];
    }

    /**
     * @param string $stateStatus
     * @param string $relatedStatus
     */
    #[DataProvider('afterSetStatusResumeDataProvider')]
    public function testAfterSetStatusResume($stateStatus, $relatedStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;

        $state = $this->createMock(StateInterface::class);

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->createMock(StateInterface::class);

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('enabled');

        $relatedViewState->method('getStatus')->willReturn($relatedStatus);

        $relatedViewState->expects($this->once())->method('save')->willReturnSelf();

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            'idle'
        )->willReturnSelf(
        );

        $relatedViewChangelog = $this->createMock(ChangelogInterface::class);

        $model = new MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public static function afterSetStatusSkipDataProvider()
    {
        return [
            ['idle', 'idle'],
            ['working', 'working'],
            ['suspended', 'suspended'],
            ['idle', 'working'],
            ['working', 'idle']
        ];
    }

    /**
     * @param string $stateStatus
     * @param string $relatedStatus
     */
    #[DataProvider('afterSetStatusSkipDataProvider')]
    public function testAfterSetStatusSkip($stateStatus, $relatedStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;

        $state = $this->createMock(StateInterface::class);

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->createMock(StateInterface::class);

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('enabled');

        $relatedViewState->method('getStatus')->willReturn($relatedStatus);

        $relatedViewState->expects($this->never())->method('save');

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects($this->never())->method('setStatus');

        $relatedViewChangelog = $this->createMock(ChangelogInterface::class);

        $model = new MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public static function afterSetStatusDisabledDataProvider()
    {
        return [['idle'], ['working'], ['suspended']];
    }

    /**
     * @param string $stateStatus
     */
    #[DataProvider('afterSetStatusDisabledDataProvider')]
    public function testAfterSetStatusDisabled($stateStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;

        $state = $this->createMock(StateInterface::class);

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->createMock(StateInterface::class);

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('disabled');

        $relatedViewState->expects($this->never())->method('getStatus');

        $relatedViewState->expects($this->never())->method('save');

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects($this->never())->method('setStatus');

        $relatedViewChangelog = $this->createMock(ChangelogInterface::class);

        $model = new MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(StateInterface::class, $model->afterSetStatus($state));
    }
}
