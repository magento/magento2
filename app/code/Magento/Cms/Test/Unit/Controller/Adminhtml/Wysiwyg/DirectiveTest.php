<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Wysiwyg;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive;
use Magento\Cms\Model\Template\Filter;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Image\Adapter\AdapterInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DirectiveTest extends TestCase
{
    const IMAGE_PATH = 'pub/media/wysiwyg/image.jpg';

    /**
     * @var Directive
     */
    protected $wysiwygDirective;

    /**
     * @var Context|MockObject
     */
    protected $actionContextMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var DecoderInterface|MockObject
     */
    protected $urlDecoderMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Filter|MockObject
     */
    protected $templateFilterMock;

    /**
     * @var AdapterFactory|MockObject
     */
    protected $imageAdapterFactoryMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $imageAdapterMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var Config|MockObject
     */
    protected $wysiwygConfigMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var RawFactory|MockObject
     */
    protected $rawFactoryMock;

    /**
     * @var Raw|MockObject
     */
    protected $rawMock;

    /**
     * @var DriverInterface|MockObject
     */
    private $driverMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->actionContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlDecoderMock = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->templateFilterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageAdapterFactoryMock = $this->getMockBuilder(AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageAdapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getColorAt',
                    'getImage',
                    'watermark',
                    'refreshImageDimensions',
                    'checkDependencies',
                    'createPngFromString',
                    'open',
                    'resize',
                    'crop',
                    'save',
                    'rotate'
                ]
            )
            ->addMethods(['getMimeType'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendResponse'])
            ->addMethods(['setHeader', 'setBody'])
            ->getMockForAbstractClass();
        $this->wysiwygConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->rawFactoryMock = $this->getMockBuilder(RawFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rawMock = $this->getMockBuilder(Raw::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionContextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->actionContextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->actionContextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->driverMock = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryWrite->expects($this->any())->method('getDriver')->willReturn($this->driverMock);
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->any())->method('getDirectoryWrite')->willReturn($directoryWrite);

        $objectManager = new ObjectManager($this);
        $this->wysiwygDirective = $objectManager->getObject(
            Directive::class,
            [
                'context' => $this->actionContextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'resultRawFactory' => $this->rawFactoryMock,
                'adapterFactory' => $this->imageAdapterFactoryMock,
                'logger' => $this->loggerMock,
                'config' => $this->wysiwygConfigMock,
                'filter' => $this->templateFilterMock,
                'filesystem' => $filesystemMock
            ]
        );
    }

    /**
     * @return void
     * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive::execute
     */
    public function testExecute(): void
    {
        $mimeType = 'image/jpeg';
        $imageBody = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $this->prepareExecuteTest();

        $this->imageAdapterMock->expects($this->once())
            ->method('open')
            ->with(self::IMAGE_PATH);
        $this->imageAdapterMock->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn($mimeType);
        $this->rawMock->expects($this->atLeastOnce())
            ->method('setHeader')
            ->with('Content-Type', $mimeType)
            ->willReturnSelf();
        $this->rawMock->expects($this->atLeastOnce())
            ->method('setContents')
            ->with($imageBody)
            ->willReturnSelf();
        $this->imageAdapterMock->expects($this->once())
            ->method('getImage')
            ->willReturn($imageBody);
        $this->driverMock->expects($this->once())
            ->method('fileGetContents')
            ->willReturn($imageBody);
        $this->rawFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rawMock);
        $this->imageAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->imageAdapterMock);

        $this->assertSame(
            $this->rawMock,
            $this->wysiwygDirective->execute()
        );
    }

    /**
     * @return void
     * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive::execute
     */
    public function testExecuteException(): void
    {
        $exception = new Exception('epic fail');
        $placeholderPath = 'pub/static/adminhtml/Magento/backend/en_US/Magento_Cms/images/wysiwyg_skin_image.png';
        $mimeType = 'image/png';
        $imageBody = '0123456789abcdefghijklmnopqrstuvwxyz';
        $this->prepareExecuteTest();

        $this->wysiwygConfigMock->expects($this->once())
            ->method('getSkinImagePlaceholderPath')
            ->willReturn($placeholderPath);
        $this->imageAdapterMock
            ->method('open')
            ->withConsecutive([self::IMAGE_PATH])
            ->willThrowException($exception);
        $this->imageAdapterMock->expects($this->atLeastOnce())
            ->method('getMimeType')
            ->willReturn($mimeType);
        $this->rawMock->expects($this->atLeastOnce())
            ->method('setHeader')
            ->with('Content-Type', $mimeType)
            ->willReturnSelf();
        $this->rawMock->expects($this->atLeastOnce())
            ->method('setContents')
            ->with($imageBody)
            ->willReturnSelf();
        $this->imageAdapterMock->expects($this->any())
            ->method('getImage')
            ->willReturn($imageBody);
        $this->driverMock->expects($this->once())
            ->method('fileGetContents')
            ->willReturn($imageBody);
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($exception);
        $this->rawFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rawMock);

        $this->imageAdapterFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturn($this->imageAdapterMock);

        $this->assertSame(
            $this->rawMock,
            $this->wysiwygDirective->execute()
        );
    }

    /**
     * @return void
     */
    protected function prepareExecuteTest(): void
    {
        $directiveParam = 'e3ttZWRpYSB1cmw9Ind5c2l3eWcvYnVubnkuanBnIn19';
        $directive = '{{media url="wysiwyg/image.jpg"}}';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('___directive')
            ->willReturn($directiveParam);
        $this->urlDecoderMock->expects($this->once())
            ->method('decode')
            ->with($directiveParam)
            ->willReturn($directive);

        $this->templateFilterMock->expects($this->once())
            ->method('filter')
            ->with($directive)
            ->willReturn(self::IMAGE_PATH);

        $this->imageAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->imageAdapterMock);
    }

    /**
     * Test Execute With Deleted Image
     *
     * @return void
     * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive::execute
     */
    public function testExecuteWithDeletedImage(): void
    {
        $exception = new Exception('epic fail');
        $placeholderPath = 'pub/static/adminhtml/Magento/backend/en_US/Magento_Cms/images/wysiwyg_skin_image.png';
        $this->prepareExecuteTest();

        $this->imageAdapterMock->expects($this->any())
            ->method('open')
            ->with(self::IMAGE_PATH)
            ->willThrowException($exception);

        $this->wysiwygConfigMock->expects($this->once())
            ->method('getSkinImagePlaceholderPath')
            ->willReturn($placeholderPath);

        $this->imageAdapterMock->expects($this->any())
            ->method('open')
            ->with($placeholderPath)
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with($exception);

        $this->rawMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', null)
            ->willReturnSelf();

        $this->rawMock->expects($this->once())
            ->method('setContents')
            ->with(null)
            ->willReturnSelf();

        $this->rawFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rawMock);

        $this->wysiwygDirective->execute();
    }
}
