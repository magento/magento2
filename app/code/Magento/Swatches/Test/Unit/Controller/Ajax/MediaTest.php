<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Controller\Ajax;

/**
 * Class Media
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    protected $mediaGallery;

    /** @var \Magento\Swatches\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $swatchHelperMock;

    /** @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productModelFactoryMock;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productMock;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMock;

    /** @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\App\Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\Magento\Swatches\Controller\Ajax\Media */
    protected $controller;

    protected function setUp()
    {
        $this->mediaGallery = [
            'image' => '/m/a/magento.png',
            'small_image' => '/m/a/magento.png',
            'thumbnail' => '/m/a/magento.png',
            'swatch_image' => '/m/a/magento.png',
        ];

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->swatchHelperMock = $this->getMock('\Magento\Swatches\Helper\Data', [], [], '', false);
        $this->productModelFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->attributeMock = $this->getMock('\Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);
        $this->contextMock = $this->getMock('\Magento\Framework\App\Action\Context', [], [], '', false);

        $this->requestMock = $this->getMock('\Magento\Framework\App\Request', ['getParam'], [], '', false);
        $this->requestMock->expects($this->any())->method('getParam')->withConsecutive(
            ['product_id'],
            ['attributes'],
            ['additional']
        )->willReturnOnConsecutiveCalls(
            59,
            ['size' => 454],
            ['color' => 43]
        );
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->resultFactory = $this->getMock('\Magento\Framework\Controller\ResultFactory', ['create'], [], '', false);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactory);

        $this->jsonMock = $this->getMock('\Magento\Framework\Controller\Result\Json', [], [], '', false);
        $this->resultFactory->expects($this->once())->method('create')->with('json')->willReturn($this->jsonMock);

        $this->controller = $this->objectManager->getObject(
            '\Magento\Swatches\Controller\Ajax\Media',
            [
                'context' => $this->contextMock,
                'swatchHelper' => $this->swatchHelperMock,
                'productModelFactory' => $this->productModelFactoryMock
            ]
        );
    }

    public function testExecute()
    {
        $this->attributeMock
            ->expects($this->any())
            ->method('offsetGet')
            ->with('attribute_code')
            ->willReturn('color');

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
            ->method('getAttributesFromConfigurable')
            ->with($this->productMock)
            ->willReturn([$this->attributeMock]);

        $this->swatchHelperMock
            ->expects($this->once())
            ->method('loadVariationByFallback')
            ->with($this->productMock, ['size' => 454, 'color' => 43])
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

        $this->assertInstanceOf('\Magento\Framework\Controller\Result\Json', $result);
    }

    public function testExecuteNullProduct()
    {
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
            ->method('getAttributesFromConfigurable')
            ->with($this->productMock)
            ->willReturn([$this->attributeMock]);

        $this->swatchHelperMock
            ->expects($this->once())
            ->method('loadVariationByFallback')
            ->with($this->productMock, ['size' => 454])
            ->willReturn(null);

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

        $this->assertInstanceOf('\Magento\Framework\Controller\Result\Json', $result);
    }
}
