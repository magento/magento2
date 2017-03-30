<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Test\Unit\Controller\Adminhtml\Sitemap;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sitemap\Controller\Adminhtml\Sitemap\Delete
     */
    private $deleteController;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \Magento\Sitemap\Model\SitemapFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->sitemapFactoryMock = $this->getMockBuilder(\Magento\Sitemap\Model\SitemapFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->deleteController = $objectManagerHelper->getObject(
            \Magento\Sitemap\Controller\Adminhtml\Sitemap\Delete::class,
            [
                'request' => $this->requestMock,
                'response' => $this->responseMock,
                'messageManager' => $this->messageManagerMock,
                'filesystem' => $this->filesystemMock,
                'sitemapFactory' => $this->sitemapFactoryMock
            ]
        );
    }

    public function testExecuteWithoutSitemapId()
    {
        $writeDirectoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->willReturn($writeDirectoryMock);
        $this->requestMock->expects($this->any())->method('getParam')->with('sitemap_id')->willReturn(0);
        $this->responseMock->expects($this->once())->method('setRedirect');
        $this->messageManagerMock->expects($this->any())
            ->method('addError')
            ->with('We can\'t find a sitemap to delete.');

        $this->deleteController->execute();
    }

    public function testExecuteCannotFindSitemap()
    {
        $id = 1;
        $writeDirectoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->willReturn($writeDirectoryMock);
        $this->requestMock->expects($this->any())->method('getParam')->with('sitemap_id')->willReturn($id);

        $sitemapMock = $this->getMockBuilder(\Magento\Sitemap\Model\Sitemap::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock();

        $sitemapMock->expects($this->once())->method('load')->with($id)->willThrowException(new \Exception);
        $this->sitemapFactoryMock->expects($this->once())->method('create')->willReturn($sitemapMock);
        $this->responseMock->expects($this->once())->method('setRedirect');
        $this->messageManagerMock->expects($this->any())
            ->method('addError');

        $this->deleteController->execute();
    }

    public function testExecute()
    {
        $id = 1;
        $sitemapPath = '/';
        $sitemapFilename = 'sitemap.xml';
        $relativePath = '/sitemap.xml';
        $writeDirectoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->willReturn($writeDirectoryMock);
        $this->requestMock->expects($this->any())->method('getParam')->with('sitemap_id')->willReturn($id);

        $sitemapMock = $this->getMockBuilder(\Magento\Sitemap\Model\Sitemap::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSitemapPath', 'getSitemapFilename', 'load', 'delete'])
            ->getMock();
        $sitemapMock->expects($this->once())->method('load')->with($id);
        $sitemapMock->expects($this->once())->method('delete');
        $sitemapMock->expects($this->any())->method('getSitemapPath')->willReturn($sitemapPath);
        $sitemapMock->expects($this->any())->method('getSitemapFilename')->willReturn($sitemapFilename);
        $this->sitemapFactoryMock->expects($this->once())->method('create')->willReturn($sitemapMock);
        $writeDirectoryMock->expects($this->any())
            ->method('getRelativePath')
            ->with($sitemapPath . $sitemapFilename)
            ->willReturn($relativePath);
        $writeDirectoryMock->expects($this->once())->method('isFile')->with($relativePath)->willReturn(true);
        $writeDirectoryMock->expects($this->once())->method('delete')->with($relativePath)->willReturn(true);

        $this->responseMock->expects($this->once())->method('setRedirect');
        $this->messageManagerMock->expects($this->any())
            ->method('addSuccess');

        $this->deleteController->execute();
    }
}
