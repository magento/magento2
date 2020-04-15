<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

class MviewStateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function afterSetStatusSuspendDataProvider()
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
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;
        $relatedVersion = 'related_version';

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
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
            $this->equalTo($relatedVersion)
        )->willReturnSelf(
            
        );

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo($stateStatus)
        )->willReturnSelf(
            
        );

        $relatedViewChangelog = $this->getMockBuilder(
            \Magento\Framework\Mview\View\ChangelogInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewChangelog->expects(
            $this->once()
        )->method(
            'setViewId'
        )->with(
            $this->equalTo($relatedViewId)
        )->willReturnSelf(
            
        );

        $relatedViewChangelog->expects($this->once())->method('getVersion')->willReturn($relatedVersion);

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(\Magento\Framework\Mview\View\StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function afterSetStatusResumeDataProvider()
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
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
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
            $this->equalTo('idle')
        )->willReturnSelf(
            
        );

        $relatedViewChangelog = $this->getMockBuilder(
            \Magento\Framework\Mview\View\ChangelogInterface::class
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(\Magento\Framework\Mview\View\StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function afterSetStatusSkipDataProvider()
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
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
        )->willReturnSelf(
            
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('enabled');

        $relatedViewState->expects($this->any())->method('getStatus')->willReturn($relatedStatus);

        $relatedViewState->expects($this->never())->method('save');

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects($this->never())->method('setStatus');

        $relatedViewChangelog = $this->getMockBuilder(
            \Magento\Framework\Mview\View\ChangelogInterface::class
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(\Magento\Framework\Mview\View\StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function afterSetStatusDisabledDataProvider()
    {
        return [['idle'], ['working'], ['suspended']];
    }

    /**
     * @param string $stateStatus
     * @dataProvider afterSetStatusDisabledDataProvider
     */
    public function testAfterSetStatusDisabled($stateStatus)
    {
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->willReturn($stateViewId);

        $state->expects($this->any())->method('getStatus')->willReturn($stateStatus);

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
        )->willReturnSelf(
            
        );

        $relatedViewState->expects($this->once())->method('getMode')->willReturn('disabled');

        $relatedViewState->expects($this->never())->method('getStatus');

        $relatedViewState->expects($this->never())->method('save');

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects($this->never())->method('setStatus');

        $relatedViewChangelog = $this->getMockBuilder(
            \Magento\Framework\Mview\View\ChangelogInterface::class
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(\Magento\Framework\Mview\View\StateInterface::class, $model->afterSetStatus($state));
    }
}
