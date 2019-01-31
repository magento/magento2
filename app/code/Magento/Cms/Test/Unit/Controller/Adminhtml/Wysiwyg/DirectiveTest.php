<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Wysiwyg;

/**
 * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DirectiveTest extends \PHPUnit\Framework\TestCase
{
    const IMAGE_PATH = 'pub/media/wysiwyg/image.jpg';

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive
     */
    protected $wysiwygDirective;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionContextMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Url\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlDecoderMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Cms\Model\Template\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateFilterMock;

    /**
     * @var \Magento\Framework\Image\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageAdapterFactoryMock;

    /**
     * @var \Magento\Framework\Image\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageAdapterMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wysiwygConfigMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rawMock;

    protected function setUp()
    {
        $this->actionContextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlDecoderMock = $this->getMockBuilder(\Magento\Framework\Url\DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFilterMock = $this->getMockBuilder(\Magento\Cms\Model\Template\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageAdapterFactoryMock = $this->getMockBuilder(\Magento\Framework\Image\AdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageAdapterMock = $this->getMockBuilder(\Magento\Framework\Image\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getMimeType',
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
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHeader', 'setBody', 'sendResponse'])
            ->getMock();
        $this->wysiwygConfigMock = $this->getMockBuilder(\Magento\Cms\Model\Wysiwyg\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->rawFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->rawMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->wysiwygDirective = $objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive::class,
            [
                'context' => $this->actionContextMock,
                'urlDecoder' => $this->urlDecoderMock,
                'resultRawFactory' => $this->rawFactoryMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive::execute
     */
    public function testExecute()
    {
        $mimeType = 'image/jpeg';
        $imageBody = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $this->prepareExecuteTest();

        $this->imageAdapterMock->expects($this->once())
            ->method('open')
            ->with(self::IMAGE_PATH);
        $this->imageAdapterMock->expects($this->once())
            ->method('getMimeType')
            ->willReturn($mimeType);
        $this->rawMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', $mimeType)
            ->willReturnSelf();
        $this->rawMock->expects($this->once())
            ->method('setContents')
            ->with($imageBody)
            ->willReturnSelf();
        $this->imageAdapterMock->expects($this->once())
            ->method('getImage')
            ->willReturn($imageBody);
        $this->rawFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rawMock);

        $this->assertSame(
            $this->rawMock,
            $this->wysiwygDirective->execute()
        );
    }

    /**
     * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive::execute
     */
    public function testExecuteException()
    {
        $exception = new \Exception('epic fail');
        $placeholderPath = 'pub/static/adminhtml/Magento/backend/en_US/Magento_Cms/images/wysiwyg_skin_image.png';
        $mimeType = 'image/png';
        $imageBody = '0123456789abcdefghijklmnopqrstuvwxyz';
        $this->prepareExecuteTest();

        $this->imageAdapterMock->expects($this->at(0))
            ->method('open')
            ->with(self::IMAGE_PATH)
            ->willThrowException($exception);
        $this->wysiwygConfigMock->expects($this->once())
            ->method('getSkinImagePlaceholderPath')
            ->willReturn($placeholderPath);
        $this->imageAdapterMock->expects($this->at(1))
            ->method('open')
            ->with($placeholderPath);
        $this->imageAdapterMock->expects($this->once())
            ->method('getMimeType')
            ->willReturn($mimeType);
        $this->rawMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', $mimeType)
            ->willReturnSelf();
        $this->rawMock->expects($this->once())
            ->method('setContents')
            ->with($imageBody)
            ->willReturnSelf();
        $this->imageAdapterMock->expects($this->once())
            ->method('getImage')
            ->willReturn($imageBody);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->rawFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->rawMock);

        $this->assertSame(
            $this->rawMock,
            $this->wysiwygDirective->execute()
        );
    }

    protected function prepareExecuteTest()
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
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Cms\Model\Template\Filter::class)
            ->willReturn($this->templateFilterMock);
        $this->templateFilterMock->expects($this->once())
            ->method('filter')
            ->with($directive)
            ->willReturn(self::IMAGE_PATH);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [\Magento\Framework\Image\AdapterFactory::class, $this->imageAdapterFactoryMock],
                    [\Magento\Cms\Model\Wysiwyg\Config::class, $this->wysiwygConfigMock],
                    [\Psr\Log\LoggerInterface::class, $this->loggerMock]
                ]
            );
        $this->imageAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->imageAdapterMock);
    }
}
