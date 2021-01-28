<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class ExportVarnishConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\PageCache\Controller\Adminhtml\PageCache\ExportVarnishConfig
     */
    protected $action;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * Set up before test
     */
    protected function setUp(): void
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

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

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

        $this->configMock->expects($this->once())->method('getVclFile')->willReturn($fileContent);
        $this->fileFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($filename),
            $this->equalTo($fileContent),
            $this->equalTo(DirectoryList::VAR_DIR)
        )->willReturn(
            $responseMock
        );

        $result = $this->action->execute();
        $this->assertInstanceOf(\Magento\Framework\App\ResponseInterface::class, $result);
    }
}
