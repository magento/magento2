<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Controller\Ajax;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Swatches\Controller\Ajax\Media;
use Magento\Swatches\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    /** @var array */
    private $mediaGallery;

    /** @var Data|MockObject */
    private $swatchHelperMock;

    /** @var ProductFactory|MockObject */
    private $productModelFactoryMock;

    /** @var Config|MockObject */
    private $config;

    /** @var Product|MockObject */
    private $productMock;

    /** @var Context|MockObject */
    private $contextMock;

    /** @var RequestInterface|MockObject */
    private $requestMock;

    /** @var ResponseInterface|MockObject */
    private $responseMock;

    /** @var ResultFactory|MockObject */
    private $resultFactory;

    /** @var Json|MockObject */
    private $jsonMock;

    /** @var ObjectManager */
    private $objectManager;

    /** @var ObjectManager|Media */
    private $controller;

    /** @var int */
    private $productId = 23;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->mediaGallery = [
            'image' => '/m/a/magento.png',
            'small_image' => '/m/a/magento.png',
            'thumbnail' => '/m/a/magento.png',
            'swatch_image' => '/m/a/magento.png',
        ];

        $this->objectManager = new ObjectManager($this);

        $this->swatchHelperMock = $this->createMock(Data::class);
        $this->productModelFactoryMock = $this->createPartialMock(
            ProductFactory::class,
            ['create']
        );
        $this->config = $this->createMock(Config::class);
        $this->config->method('getTtl')->willReturn(1);

        $this->productMock = $this->createMock(Product::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setPublicHeaders'])
            ->getMockForAbstractClass();
        $this->responseMock->method('setPublicHeaders')->willReturnSelf();
        $this->contextMock->method('getResponse')->willReturn($this->responseMock);
        $this->resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactory);

        $this->jsonMock = $this->createMock(Json::class);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonMock);

        $this->controller = $this->objectManager->getObject(
            Media::class,
            [
                'context' => $this->contextMock,
                'swatchHelper' => $this->swatchHelperMock,
                'productModelFactory' => $this->productModelFactoryMock,
                'config' => $this->config
            ]
        );
    }

    /**
     * Prepare product mock for test execution.
     *
     * @return void
     */
    private function prepareProductMock(): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('product_id')
            ->willReturn($this->productId);
        $this->productModelFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('load')
            ->with($this->productId)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getIdentities')
            ->willReturn(['tags']);
    }

    /**
     * Check that controller return media gallery for the product.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->prepareProductMock();
        $this->productMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->productId);
        $this->productMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::STATUS_ENABLED);

        $this->swatchHelperMock
            ->expects($this->once())
            ->method('getProductMediaGallery')
            ->with($this->productMock)
            ->willReturn($this->mediaGallery);

        $this->jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with($this->mediaGallery)->willReturnSelf();

        $result = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $result);
    }

    /**
     * Check that controller does not crash while taking the non-existing product.
     *
     * @return void
     */
    public function testExecuteNonExistingProduct()
    {
        $this->prepareProductMock();
        $this->productMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with([])->willReturnSelf();

        $result = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $result);
    }

    /**
     * Check that controller does not return media gallery for disabled product.
     *
     * @return void
     */
    public function testExecuteDisabledProduct()
    {
        $this->prepareProductMock();
        $this->productMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->productId);

        $this->productMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::STATUS_DISABLED);

        $this->jsonMock
            ->expects($this->once())
            ->method('setData')
            ->with([])->willReturnSelf();

        $result = $this->controller->execute();

        $this->assertInstanceOf(Json::class, $result);
    }
}
