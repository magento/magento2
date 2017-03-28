<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

class MviewStateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function testAfterSetStatusSuspendDataProvider()
    {
        return [['suspended', 'idle'], ['suspended', 'working']];
    }

    /**
     * @param string $stateStatus
     * @param string $relatedStatus
     * @dataProvider testAfterSetStatusSuspendDataProvider
     */
    public function testAfterSetStatusSuspend($stateStatus, $relatedStatus)
    {
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;
        $relatedVersion = 'related_version';

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
        )->will(
            $this->returnSelf()
        );

        $relatedViewState->expects($this->once())->method('getMode')->will($this->returnValue('enabled'));

        $relatedViewState->expects($this->once())->method('getStatus')->will($this->returnValue($relatedStatus));

        $relatedViewState->expects($this->once())->method('save')->will($this->returnSelf());

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setVersionId'
        )->with(
            $this->equalTo($relatedVersion)
        )->will(
            $this->returnSelf()
        );

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo($stateStatus)
        )->will(
            $this->returnSelf()
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
        )->will(
            $this->returnSelf()
        );

        $relatedViewChangelog->expects($this->once())->method('getVersion')->will($this->returnValue($relatedVersion));

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf(\Magento\Framework\Mview\View\StateInterface::class, $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function testAfterSetStatusResumeDataProvider()
    {
        return [['idle', 'suspended'], ['working', 'suspended']];
    }

    /**
     * @param string $stateStatus
     * @param string $relatedStatus
     * @dataProvider testAfterSetStatusResumeDataProvider
     */
    public function testAfterSetStatusResume($stateStatus, $relatedStatus)
    {
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
        )->will(
            $this->returnSelf()
        );

        $relatedViewState->expects($this->once())->method('getMode')->will($this->returnValue('enabled'));

        $relatedViewState->expects($this->any())->method('getStatus')->will($this->returnValue($relatedStatus));

        $relatedViewState->expects($this->once())->method('save')->will($this->returnSelf());

        $relatedViewState->expects($this->never())->method('setVersionId');

        $relatedViewState->expects(
            $this->once()
        )->method(
            'setStatus'
        )->with(
            $this->equalTo('idle')
        )->will(
            $this->returnSelf()
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
    public function testAfterSetStatusSkipDataProvider()
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
     * @dataProvider testAfterSetStatusSkipDataProvider
     */
    public function testAfterSetStatusSkip($stateStatus, $relatedStatus)
    {
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
        )->will(
            $this->returnSelf()
        );

        $relatedViewState->expects($this->once())->method('getMode')->will($this->returnValue('enabled'));

        $relatedViewState->expects($this->any())->method('getStatus')->will($this->returnValue($relatedStatus));

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
    public function testAfterSetStatusDisabledDataProvider()
    {
        return [['idle'], ['working'], ['suspended']];
    }

    /**
     * @param string $stateStatus
     * @dataProvider testAfterSetStatusDisabledDataProvider
     */
    public function testAfterSetStatusDisabled($stateStatus)
    {
        $stateViewId = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $relatedViewId = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

        $state = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            \Magento\Framework\Mview\View\StateInterface::class
        )->disableOriginalConstructor()->getMock();

        $relatedViewState->expects(
            $this->once()
        )->method(
            'loadByView'
        )->with(
            $this->equalTo($relatedViewId)
        )->will(
            $this->returnSelf()
        );

        $relatedViewState->expects($this->once())->method('getMode')->will($this->returnValue('disabled'));

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
