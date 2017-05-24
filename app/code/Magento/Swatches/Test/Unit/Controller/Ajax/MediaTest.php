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
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $mediaGallery;

    /** @var \Magento\Swatches\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    private $swatchHelperMock;

    /** @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $productModelFactoryMock;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    private $productMock;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $contextMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $resultFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $jsonMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\Magento\Swatches\Controller\Ajax\Media */
    private $controller;

    protected function setUp()
    {
        $this->mediaGallery = [
            'image' => '/m/a/magento.png',
            'small_image' => '/m/a/magento.png',
            'thumbnail' => '/m/a/magento.png',
            'swatch_image' => '/m/a/magento.png',
        ];

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->swatchHelperMock = $this->getMock(\Magento\Swatches\Helper\Data::class, [], [], '', false);
        $this->productModelFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->contextMock = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);

        $this->requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->resultFactory = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactory);

        $this->jsonMock = $this->getMock(\Magento\Framework\Controller\Result\Json::class, [], [], '', false);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonMock);

        $this->controller = $this->objectManager->getObject(
            \Magento\Swatches\Controller\Ajax\Media::class,
            [
                'context' => $this->contextMock,
                'swatchHelper' => $this->swatchHelperMock,
                'productModelFactory' => $this->productModelFactoryMock
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
