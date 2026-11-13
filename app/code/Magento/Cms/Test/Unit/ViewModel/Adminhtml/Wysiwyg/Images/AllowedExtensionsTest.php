<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\ViewModel\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Cms\ViewModel\Adminhtml\Wysiwyg\Images\AllowedExtensions;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AllowedExtensionsTest extends TestCase
{
    /**
     * @var Storage|MockObject
     */
    private $storageMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var AllowedExtensions
     */
    private $viewModel;

    protected function setUp(): void
    {
        $this->storageMock = $this->createMock(Storage::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->viewModel = new AllowedExtensions($this->storageMock, $this->requestMock, new Json());
    }

    public function testExplicitMediaTypeTakesPrecedenceOverRequest(): void
    {
        $this->requestMock->expects($this->never())->method('getParam');
        $this->storageMock->expects($this->once())
            ->method('getAllowedExtensions')
            ->with('file')
            ->willReturn(['jpg', 'pdf']);

        $this->assertSame('["jpg","pdf"]', $this->viewModel->getAllowedExtensionsJson('file'));
    }

    public function testMediaTypeFallsBackToRequestParameter(): void
    {
        $this->requestMock->method('getParam')->with('type')->willReturn('image');
        $this->storageMock->expects($this->once())
            ->method('getAllowedExtensions')
            ->with('image')
            ->willReturn(['gif', 'jpg', 'jpeg', 'png']);

        $this->assertSame('["gif","jpg","jpeg","png"]', $this->viewModel->getAllowedExtensionsJson());
    }

    public function testNoMediaTypeUsesStorageDefault(): void
    {
        $this->requestMock->method('getParam')->with('type')->willReturn(null);
        $this->storageMock->expects($this->once())
            ->method('getAllowedExtensions')
            ->with(null)
            ->willReturn(['gif', 'pdf']);

        $this->assertSame('["gif","pdf"]', $this->viewModel->getAllowedExtensionsJson());
    }

    public function testNonStringRequestValueIsIgnored(): void
    {
        $this->requestMock->method('getParam')->with('type')->willReturn(['image']);
        $this->storageMock->expects($this->once())
            ->method('getAllowedExtensions')
            ->with(null)
            ->willReturn(['gif']);

        $this->assertSame('["gif"]', $this->viewModel->getAllowedExtensionsJson());
    }
}
