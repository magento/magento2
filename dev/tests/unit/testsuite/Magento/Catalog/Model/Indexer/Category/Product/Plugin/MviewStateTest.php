<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

class MviewStateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function testAfterSetStatusSuspendDataProvider()
    {
        return array(array('suspended', 'idle'), array('suspended', 'working'));
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
            'Magento\Framework\Mview\View\StateInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            'Magento\Framework\Mview\View\StateInterface'
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
            'Magento\Framework\Mview\View\ChangelogInterface'
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
        $this->assertInstanceOf('\Magento\Framework\Mview\View\StateInterface', $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function testAfterSetStatusResumeDataProvider()
    {
        return array(array('idle', 'suspended'), array('working', 'suspended'));
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
            'Magento\Framework\Mview\View\StateInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            'Magento\Framework\Mview\View\StateInterface'
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
            'Magento\Framework\Mview\View\ChangelogInterface'
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf('\Magento\Framework\Mview\View\StateInterface', $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function testAfterSetStatusSkipDataProvider()
    {
        return array(
            array('idle', 'idle'),
            array('working', 'working'),
            array('suspended', 'suspended'),
            array('idle', 'working'),
            array('working', 'idle')
        );
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
            'Magento\Framework\Mview\View\StateInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            'Magento\Framework\Mview\View\StateInterface'
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
            'Magento\Framework\Mview\View\ChangelogInterface'
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf('\Magento\Framework\Mview\View\StateInterface', $model->afterSetStatus($state));
    }

    /**
     * @return array
     */
    public function testAfterSetStatusDisabledDataProvider()
    {
        return array(array('idle'), array('working'), array('suspended'));
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
            'Magento\Framework\Mview\View\StateInterface'
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $state->expects($this->exactly(2))->method('getViewId')->will($this->returnValue($stateViewId));

        $state->expects($this->any())->method('getStatus')->will($this->returnValue($stateStatus));

        $relatedViewState = $this->getMockBuilder(
            'Magento\Framework\Mview\View\StateInterface'
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
            'Magento\Framework\Mview\View\ChangelogInterface'
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\MviewState(
            $relatedViewState,
            $relatedViewChangelog
        );
        $this->assertInstanceOf('\Magento\Framework\Mview\View\StateInterface', $model->afterSetStatus($state));
    }
}
