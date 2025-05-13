<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

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
     * @dataProvider afterSetStatusSuspendDataProvider
     */
    public function testAfterSetStatusSuspend($stateStatus, $relatedStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;
        $relatedVersion = 'related_version';

        $state = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMock();

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

        $relatedViewChangelog = $this->getMockBuilder(
            ChangelogInterface::class
        )->disableOriginalConstructor()
            ->getMock();

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
     * @dataProvider afterSetStatusResumeDataProvider
     */
    public function testAfterSetStatusResume($stateStatus, $relatedStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('enabled');

        $relatedViewState->expects($this->any())->method('getStatus')->willReturn($relatedStatus);

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

        $relatedViewChangelog = $this->getMockBuilder(
            ChangelogInterface::class
        )->disableOriginalConstructor()
            ->getMock();

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
     * @dataProvider afterSetStatusSkipDataProvider
     */
    public function testAfterSetStatusSkip($stateStatus, $relatedStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $relatedViewId
        )->willReturnSelf(
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('enabled');

        $relatedViewState->expects($this->any())->method('getStatus')->willReturn($relatedStatus);

        $relatedViewState->expects($this->never())->method('save');

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects($this->never())->method('setStatus');

        $relatedViewChangelog = $this->getMockBuilder(
            ChangelogInterface::class
        )->disableOriginalConstructor()
            ->getMock();

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
     * @dataProvider afterSetStatusDisabledDataProvider
     */
    public function testAfterSetStatusDisabled($stateStatus)
    {
        $stateViewId = Category::INDEXER_ID;
        $relatedViewId = Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
            ->getMock();

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

        $relatedViewChangelog = $this->getMockBuilder(
            ChangelogInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $model = new MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(StateInterface::class, $model->afterSetStatus($state));
    }
}
