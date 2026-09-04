<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Block\Adminhtml\Wysiwyg\Images\Content;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Content\Uploader;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\File\Size;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test allowed extensions exposure of the Wysiwyg Images uploader block.
 */
class UploaderTest extends TestCase
{
    /**
     * @var Uploader
     */
    private $block;

    /**
     * @var Storage|MockObject
     */
    private $imagesStorageMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();

        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getUniqueHash')->willReturn('id');
        $formKeyMock = $this->createMock(FormKey::class);
        $formKeyMock->method('getFormKey')->willReturn('form_key');
        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->method('getParam')->with('type')->willReturn('image');
        $urlBuilderMock = $this->createMock(UrlInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getMathRandom')->willReturn($randomMock);
        $contextMock->method('getFormKey')->willReturn($formKeyMock);
        $contextMock->method('getRequest')->willReturn($requestMock);
        $contextMock->method('getUrlBuilder')->willReturn($urlBuilderMock);

        $this->imagesStorageMock = $this->createMock(Storage::class);
        $this->imagesStorageMock->method('getAllowedExtensions')
            ->with('image')
            ->willReturn(['gif', 'jpg', 'jpeg', 'png', 'pdf']);

        $this->block = new Uploader(
            $contextMock,
            $this->createMock(Size::class),
            $this->imagesStorageMock,
            [],
            new Json()
        );
    }

    /**
     * @return void
     */
    public function testGetAllowedExtensionsReturnsStorageConfiguredExtensions(): void
    {
        $this->assertSame(['gif', 'jpg', 'jpeg', 'png', 'pdf'], $this->block->getAllowedExtensions());
    }

    /**
     * @return void
     */
    public function testGetAllowedExtensionsJsonReturnsSerializedExtensions(): void
    {
        $this->assertSame('["gif","jpg","jpeg","png","pdf"]', $this->block->getAllowedExtensionsJson());
    }
}
