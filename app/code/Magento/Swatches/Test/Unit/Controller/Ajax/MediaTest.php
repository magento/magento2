<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Controller\Ajax;

/**
 * Class Media
 */
class MediaTest extends \PHPUnit\Framework\TestCase
{
    /** @var array */
    private $mediaGallery;

    /** @var \Magento\Swatches\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    private $swatchHelperMock;

    /** @var \Magento\Catalog\Model\ProductFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $productModelFactoryMock;

    /** @var \Magento\PageCache\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    private $config;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject */
    private $productMock;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    private $contextMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $responseMock;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $resultFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit\Framework\MockObject\MockObject */
    private $jsonMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\Magento\Swatches\Controller\Ajax\Media */
    private $controller;

    protected function setUp(): void
    {
        $this->mediaGallery = [
            'image' => '/m/a/magento.png',
            'small_image' => '/m/a/magento.png',
            'thumbnail' => '/m/a/magento.png',
            'swatch_image' => '/m/a/magento.png',
        ];

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->swatchHelperMock = $this->createMock(\Magento\Swatches\Helper\Data::class);
        $this->productModelFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create']
        );
        $this->config = $this->createMock(\Magento\PageCache\Model\Config::class);
        $this->config->method('getTtl')->willReturn(1);

        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);

        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPublicHeaders'])
            ->getMockForAbstractClass();
        $this->responseMock->method('setPublicHeaders')->willReturnSelf();
        $this->contextMock->method('getResponse')->willReturn($this->responseMock);
        $this->resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactory);

        $this->jsonMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonMock);

        $this->controller = $this->objectManager->getObject(
            \Magento\Swatches\Controller\Ajax\Media::class,
            [
                'context' => $this->contextMock,
                'swatchHelper' => $this->swatchHelperMock,
                'productModelFactory' => $this->productModelFactoryMock,
                'config' => $this->config
            ]
        );
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->any())->method('getParam')->with('product_id')->willReturn(59);
        $this->productMock
            ->expects($this->once())
            ->method('load')
            ->with(59)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getIdentities')
            ->willReturn(['tags']);

        $this->productModelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);

        $this->swatchHelperMock
            ->expects($this->once())
            ->method('getProductMediaGallery')
            ->with($this->productMock)
            ->willReturn($this->mediaGallery);

        $this->jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with($this->mediaGallery)
            ->will($this->returnSelf());

        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $result);
    }
}
