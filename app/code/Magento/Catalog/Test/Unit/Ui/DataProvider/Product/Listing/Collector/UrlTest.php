<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Url */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Catalog\Block\Product\AbstractProduct|\PHPUnit_Framework_MockObject_MockObject */
    protected $abstractProductMock;

    /** @var \Magento\Catalog\Helper\Product\Compare|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogProductHelperMock;

    /** @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $postHelperMock;

    /** @var ButtonInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $buttonFactoryMock;

    /** @var  ButtonInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $buttonMock;

    protected function setUp()
    {
        $this->abstractProductMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\AbstractProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProductHelperMock = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Compare::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->postHelperMock = $this->getMockBuilder(\Magento\Framework\Data\Helper\PostHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->buttonFactoryMock = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->buttonMock = $this->getMockBuilder(ButtonInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Url::class,
            [
                'abstractProduct' => $this->abstractProductMock,
                'compare' => $this->catalogProductHelperMock,
                'postHelper' => $this->postHelperMock,
                'buttonFactory' =>$this->buttonFactoryMock,
            ]
        );
    }

    public function testGet()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);
        $this->catalogProductHelperMock
            ->expects($this->once())
            ->method('getPostDataParams')
            ->with($product)
            ->willReturn(['Some compare Data']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->buttonFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->buttonMock);
        $this->abstractProductMock->expects($this->exactly(2))
            ->method('getAddToCartUrl')
            ->with(
                $product,
                ['useUencPlaceholder' => true]
            )
            ->willReturn('some:url');
        $this->postHelperMock->expects($this->once())
            ->method('getPostData')
            ->with(
                'some:url',
                [
                    'product' => 1,
                    \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => "%uenc%"
                ]
            )
            ->willReturn(['some cart url post data']);

        $this->model->collect($product, $productRenderInfoDto);
    }
}
