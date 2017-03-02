<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject| \Magento\Marketplace\Controller\Adminhtml\Index\Index
     */
    private $indexControllerMock;

    protected function setUp()
    {
        $this->indexControllerMock = $this->getControllerIndexMock(['getResultPageFactory']);
    }

    /**
     * @covers \Magento\Marketplace\Controller\Adminhtml\Index\Index::execute
     */
    public function testExecute()
    {
        $pageMock = $this->getPageMock(['setActiveMenu', 'addBreadcrumb', 'getConfig']);
        $pageMock->expects($this->once())
            ->method('setActiveMenu');
        $pageMock->expects($this->once())
            ->method('addBreadcrumb');

        $resultPageFactoryMock = $this->getResultPageFactoryMock(['create']);

        $resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($pageMock));

        $this->indexControllerMock->expects($this->once())
            ->method('getResultPageFactory')
            ->will($this->returnValue($resultPageFactoryMock));

        $titleMock = $this->getTitleMock(['prepend']);
        $titleMock->expects($this->once())
            ->method('prepend');
        $configMock =  $this->getConfigMock(['getTitle']);
        $configMock->expects($this->once())
            ->method('getTitle')
            ->will($this->returnValue($titleMock));
        $pageMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($configMock));

        $this->indexControllerMock->execute();
    }

    /**
     * Gets index controller mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Controller\Adminhtml\Index\Index
     */
    public function getControllerIndexMock($methods = null)
    {
        return $this->getMock(\Magento\Marketplace\Controller\Adminhtml\Index\Index::class, $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactoryMock($methods = null)
    {
        return $this->getMock(\Magento\Framework\View\Result\PageFactory::class, $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Page\Config
     */
    public function getConfigMock($methods = null)
    {
        return $this->getMock(\Magento\Framework\View\Page\Config::class, $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Page\Title
     */
    public function getTitleMock($methods = null)
    {
        return $this->getMock(\Magento\Framework\View\Page\Title::class, $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Page\Title
     */
    public function getPageMock($methods = null)
    {
        return $this->getMock(\Magento\Framework\View\Result\Page::class, $methods, [], '', false);
    }
}
