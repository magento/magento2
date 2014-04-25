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
namespace Magento\Framework\Mview;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Mview\View\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewsFactoryMock;

    protected function setUp()
    {
        $this->viewsFactoryMock = $this->getMock(
            'Magento\Framework\Mview\View\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->model = new \Magento\Framework\Mview\Processor($this->viewsFactoryMock);
    }

    /**
     * Return array of mocked views
     *
     * @param string $method
     * @return \Magento\Framework\Mview\ViewInterface[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function getViews($method)
    {
        $viewMock = $this->getMock('Magento\Framework\Mview\ViewInterface', array(), array(), '', false);
        $viewMock->expects($this->exactly(2))->method($method);
        return array($viewMock, $viewMock);
    }

    /**
     * Return view collection mock
     *
     * @return \Magento\Framework\Mview\View\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getViewsMock()
    {
        $viewsMock = $this->getMock('Magento\Framework\Mview\View\Collection', array(), array(), '', false);
        $this->viewsFactoryMock->expects($this->once())->method('create')->will($this->returnValue($viewsMock));
        return $viewsMock;
    }

    public function testUpdate()
    {
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects($this->once())->method('getItems')->will($this->returnValue($this->getViews('update')));
        $viewsMock->expects($this->never())->method('getItemsByColumnValue');

        $this->model->update();
    }

    public function testUpdateWithGroup()
    {
        $group = 'group';
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects($this->never())->method('getItems');
        $viewsMock->expects(
            $this->once()
        )->method(
            'getItemsByColumnValue'
        )->with(
            $group
        )->will(
            $this->returnValue($this->getViews('update'))
        );

        $this->model->update($group);
    }

    public function testClearChangelog()
    {
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects(
            $this->once()
        )->method(
            'getItems'
        )->will(
            $this->returnValue($this->getViews('clearChangelog'))
        );
        $viewsMock->expects($this->never())->method('getItemsByColumnValue');

        $this->model->clearChangelog();
    }

    public function testClearChangelogWithGroup()
    {
        $group = 'group';
        $viewsMock = $this->getViewsMock();
        $viewsMock->expects($this->never())->method('getItems');
        $viewsMock->expects(
            $this->once()
        )->method(
            'getItemsByColumnValue'
        )->with(
            $group
        )->will(
            $this->returnValue($this->getViews('clearChangelog'))
        );

        $this->model->clearChangelog($group);
    }
}
