<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo\Test\Unit\Controller\Adminhtml\Product\Gallery;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Image;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\AllowedProtocols;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;
use Magento\ProductVideo\Controller\Adminhtml\Product\Gallery\RetrieveImage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RetrieveImageTest extends TestCase
{
    /**
     * @var MockObject|Context
     */
    protected $contextMock;

    /**
     * @var MockObject|RawFactory
     */
    protected $rawFactoryMock;

    /**
     * @var MockObject|Config
     */
    protected $configMock;

    /**
     * @var MockObject|Filesystem
     */
    protected $filesystemMock;

    /**
     * @var MockObject|Image
     */
    protected $adapterMock;

    /**
     * @var MockObject|AdapterFactory
     */
    protected $adapterFactoryMock;

    /**
     * @var MockObject|Curl
     */
    protected $curlMock;

    /**
     * @var MockObject|\Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $storageFileMock;

    /**
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * @var MockObject|AbstractAdapter
     */
    protected $abstractAdapter;

    /**
     * @var RetrieveImage|MockObject
     */
    protected $image;

    /**
     * @var NotProtectedExtension|MockObject
     */
    private $validatorMock;

    /**
     * @var DriverInterface|MockObject
     */
    private $fileDriverMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);
        $this->validatorMock = $this
            ->createMock(NotProtectedExtension::class);
        $this->rawFactoryMock =
            $this->createPartialMock(RawFactory::class, ['create']);
        $response = new DataObject();
        $this->rawFactoryMock->expects($this->once())->method('create')->willReturn($response);
        $this->configMock = $this->createMock(Config::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->adapterMock =
            $this->createMock(Image::class);
        $this->adapterFactoryMock =
            $this->createPartialMock(AdapterFactory::class, ['create']);
        $this->abstractAdapter = $this->createMock(AbstractAdapter::class);
        $this->adapterFactoryMock->expects($this->once())->method('create')->willReturn($this->abstractAdapter);
        $this->curlMock = $this->createMock(Curl::class);
        $this->storageFileMock = $this->createMock(File::class);
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->fileDriverMock = $this->getMockForAbstractClass(DriverInterface::class);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->request);
        $managerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($managerMock);

        $this->image = $objectManager->getObject(
            RetrieveImage::class,
            [
                'context' => $this->contextMock,
                'resultRawFactory' => $this->rawFactoryMock,
                'mediaConfig' => $this->configMock,
                'fileSystem' => $this->filesystemMock,
                'imageAdapterFactory' => $this->adapterFactoryMock,
                'curl' => $this->curlMock,
                'fileUtility' => $this->storageFileMock,
                'protocolValidator' => new AllowedProtocols(),
                'extensionValidator' => $this->validatorMock,
                'fileDriver' => $this->fileDriverMock,
            ]
        );
    }

    /**
     * Test execute()
     */
    public function testExecute()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(
            'https://example.com/test.jpg'
        );
        $readInterface = $this->createMock(
            ReadInterface::class
        );
        $writeInterface = $this->createMock(
            WriteInterface::class
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->any())->method('validateUploadFile')->willReturn('true');
        $this->validatorMock->expects($this->once())->method('isValid')->with('jpg')->willReturn('true');
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($writeInterface);
        $this->curlMock->expects($this->once())->method('read')->willReturn('testimage');

        $this->image->execute();
    }

    /**
     * Invalid file which is not an image should cause exception to be thrown.
     */
    public function testExecuteInvalidFileImage()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(
            'https://example.com/test.jpg'
        );
        $readInterface = $this->createMock(
            ReadInterface::class,
            [],
            [],
            '',
            false
        );
        $writeInterface = $this->createMock(
            WriteInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->any())
            ->method('validateUploadFile')
            ->willThrowException(new \Exception('Invalid File.'));
        $this->validatorMock->expects($this->once())->method('isValid')->with('jpg')->willReturn('true');
        $this->curlMock->expects($this->once())->method('read')->willReturn('testimage');
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($writeInterface);
        $writeInterface->expects($this->once())->method('isExist')->willReturn('true');
        $writeInterface->expects($this->once())->method('delete')->willReturn('false');

        $this->image->execute();
    }

    /**
     * Invalid file which is an invalid file type should cause exception to be thrown.
     */
    public function testExecuteInvalidFileType()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(
            'https://example.com/test.php'
        );
        $readInterface = $this->createMock(
            ReadInterface::class,
            [],
            [],
            '',
            false
        );
        $writeInterface = $this->createMock(
            WriteInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($readInterface);
        $readInterface->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $this->abstractAdapter->expects($this->never())->method('validateUploadFile');
        $this->validatorMock->expects($this->once())->method('isValid')->with('php')->willReturn(false);
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->willReturn($writeInterface);
        $writeInterface->expects($this->never())->method('isExist');

        $this->image->execute();
    }
}
