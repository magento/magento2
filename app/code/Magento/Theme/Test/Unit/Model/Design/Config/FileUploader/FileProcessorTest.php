<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config\FileUploader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Design\Backend\File;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\FileUploader\FileProcessor;
use Magento\Theme\Model\Design\Config\MetadataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FileProcessorTest extends TestCase
{
    /** @var \Magento\MediaStorage\Model\File\UploaderFactory|MockObject */
    protected $uploaderFactory;

    /** @var Uploader|MockObject */
    protected $uploader;

    /** @var BackendModelFactory|MockObject */
    protected $backendModelFactory;

    /** @var File|MockObject */
    protected $backendModel;

    /** @var MetadataProvider|MockObject */
    protected $metadataProvider;

    /** @var WriteInterface|MockObject */
    protected $directoryWrite;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var StoreInterface|MockObject */
    protected $store;

    /** @var FileProcessor */
    protected $fileProcessor;

    protected function setUp(): void
    {
        $this->uploaderFactory = $this->getMockBuilder(\Magento\MediaStorage\Model\File\UploaderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModelFactory = $this->getMockBuilder(BackendModelFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataProvider = $this->getMockBuilder(MetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryWrite);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->fileProcessor = new FileProcessor(
            $this->uploaderFactory,
            $this->backendModelFactory,
            $this->metadataProvider,
            $filesystem,
            $this->storeManager
        );
    }

    public function testSaveToTmp()
    {
        $path = 'design/header/logo_src';
        $fieldCode = 'header_logo_src';
        $metadata = [
            $fieldCode => [
                'path' => $path,
                'backend_model' => File::class
            ],
        ];
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn('http://magento2.com/media/');
        $this->directoryWrite->expects($this->once())
            ->method('getAbsolutePath')
            ->with('tmp/' . FileProcessor::FILE_DIR)
            ->willReturn('absolute/path/to/tmp/media');

        $this->metadataProvider->expects($this->once())
            ->method('get')
            ->willReturn($metadata);
        $this->backendModelFactory->expects($this->once())
            ->method('createByPath')
            ->with($path)
            ->willReturn($this->backendModel);
        $this->uploaderFactory->expects($this->once())
            ->method('create')
            ->with(['fileId' => $fieldCode])
            ->willReturn($this->uploader);
        $this->uploader->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true);
        $this->uploader->expects($this->once())
            ->method('setFilesDispersion')
            ->with(false);

        $this->backendModel->expects($this->once())
            ->method('getAllowedExtensions')
            ->willReturn(['png', 'jpg']);
        $this->uploader->expects($this->once())
            ->method('setAllowedExtensions')
            ->with(['png', 'jpg']);
        $this->uploader->expects($this->once())
            ->method('addValidateCallback')
            ->with('size', $this->backendModel, 'validateMaxSize');
        $this->uploader->expects($this->once())
            ->method('save')
            ->with('absolute/path/to/tmp/media')
            ->willReturn([
                'file' => 'file.jpg',
                'size' => '234234',
                'type' => 'image/jpg',
                'name' => 'file.jpg',
                'path' => 'abs/path',
            ]);
        $this->assertEquals(
            [
                'file' => 'file.jpg',
                'name' => 'file.jpg',
                'size' => '234234',
                'type' => 'image/jpg',
                'url' => 'http://magento2.com/media/tmp/' . FileProcessor::FILE_DIR . '/file.jpg'
            ],
            $this->fileProcessor->saveToTmp($fieldCode)
        );
    }
}
