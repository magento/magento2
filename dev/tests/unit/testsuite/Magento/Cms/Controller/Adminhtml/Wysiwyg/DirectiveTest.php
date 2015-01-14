<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg;

/**
 * @covers \Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive
 */
class DirectiveTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->actionContextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlDecoderMock = $this->getMockBuilder('Magento\Framework\Url\DecoderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateFilterMock = $this->getMockBuilder('Magento\Cms\Model\Template\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageAdapterFactoryMock = $this->getMockBuilder('Magento\Framework\Image\AdapterFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageAdapterMock = $this->getMockBuilder('Magento\Framework\Image\Adapter\AdapterInterface')
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
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setHeader', 'setBody', 'sendResponse'])
            ->getMock();
        $this->wysiwygConfigMock = $this->getMockBuilder('Magento\Cms\Model\Wysiwyg\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
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

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->wysiwygDirective = $objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Wysiwyg\Directive',
            [
                'context' => $this->actionContextMock,
                'urlDecoder' => $this->urlDecoderMock
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
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', $mimeType)
            ->willReturnSelf();
        $this->imageAdapterMock->expects($this->once())
            ->method('getImage')
            ->willReturn($imageBody);
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($imageBody)
            ->willReturnSelf();

        $this->wysiwygDirective->execute();
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
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', $mimeType)
            ->willReturnSelf();
        $this->imageAdapterMock->expects($this->once())
            ->method('getImage')
            ->willReturn($imageBody);
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($imageBody)
            ->willReturnSelf();
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->wysiwygDirective->execute();
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
            ->with('Magento\Cms\Model\Template\Filter')
            ->willReturn($this->templateFilterMock);
        $this->templateFilterMock->expects($this->once())
            ->method('filter')
            ->with($directive)
            ->willReturn(self::IMAGE_PATH);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['Magento\Framework\Image\AdapterFactory', $this->imageAdapterFactoryMock],
                    ['Magento\Cms\Model\Wysiwyg\Config', $this->wysiwygConfigMock],
                    ['Psr\Log\LoggerInterface', $this->loggerMock]
                ]
            );
        $this->imageAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->imageAdapterMock);
    }
}
