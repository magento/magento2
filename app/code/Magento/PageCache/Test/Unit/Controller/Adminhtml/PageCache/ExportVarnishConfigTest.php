<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\PageCache\Controller\Adminhtml/PageCache
 */
namespace Magento\PageCache\Test\Unit\Controller\Adminhtml\PageCache;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class PageCacheTest
 *
 */
class ExportVarnishConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\PageCache\Controller\Adminhtml\PageCache\ExportVarnishConfig
     */
    protected $action;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->fileFactoryMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http\FileFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(
            \Magento\PageCache\Model\Config::class
        )->disableOriginalConstructor()->getMock();
        $contextMock = $this->getMockBuilder(
            \Magento\Backend\App\Action\Context::class
        )->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(
            \Magento\Framework\App\Request\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder(
            \Magento\Framework\App\View::class
        )->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));

        $this->action = new \Magento\PageCache\Controller\Adminhtml\PageCache\ExportVarnishConfig(
            $contextMock,
            $this->fileFactoryMock,
            $this->configMock
        );
    }

    public function testExportVarnishConfigAction()
    {
        $fileContent = 'some conetnt';
        $filename = 'varnish.vcl';
        $responseMock = $this->getMockBuilder(
            \Magento\Framework\App\ResponseInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->configMock->expects($this->once())->method('getVclFile')->will($this->returnValue($fileContent));
        $this->fileFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($filename),
            $this->equalTo($fileContent),
            $this->equalTo(DirectoryList::VAR_DIR)
        )->will(
            $this->returnValue($responseMock)
        );

        $result = $this->action->execute();
        $this->assertInstanceOf(\Magento\Framework\App\ResponseInterface::class, $result);
    }
}
