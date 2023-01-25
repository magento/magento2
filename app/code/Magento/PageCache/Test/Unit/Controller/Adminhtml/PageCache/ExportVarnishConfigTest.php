<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\PageCache\Controller\Adminhtml/PageCache
 */
namespace Magento\PageCache\Test\Unit\Controller\Adminhtml\PageCache;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\View;
use Magento\PageCache\Controller\Adminhtml\PageCache\ExportVarnishConfig;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PageCacheTest
 *
 */
class ExportVarnishConfigTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var ExportVarnishConfig
     */
    protected $action;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->fileFactoryMock = $this->getMockBuilder(
            FileFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(
            Config::class
        )->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(
            Context::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(
            Http::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(
            \Magento\Framework\App\Response\Http::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(
            View::class
        )->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->action = new ExportVarnishConfig(
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
            ResponseInterface::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->configMock->expects($this->once())->method('getVclFile')->willReturn($fileContent);
        $this->fileFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $filename,
            $fileContent,
            DirectoryList::VAR_DIR
        )->willReturn(
            $responseMock
        );

        $result = $this->action->execute();
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
