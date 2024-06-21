<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductRender\ButtonInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Listing\Collector\Url;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /** @var Url */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var AbstractProduct|MockObject */
    protected $abstractProductMock;

    /** @var Compare|MockObject */
    protected $catalogProductHelperMock;

    /** @var PostHelper|MockObject */
    protected $postHelperMock;

    /** @var ButtonInterfaceFactory|MockObject */
    private $buttonFactoryMock;

    /** @var  ButtonInterface|MockObject */
    private $buttonMock;

    protected function setUp(): void
    {
        $this->abstractProductMock = $this->getMockBuilder(AbstractProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProductHelperMock = $this->getMockBuilder(Compare::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->postHelperMock = $this->getMockBuilder(PostHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->buttonFactoryMock = $this->getMockBuilder(
            ButtonInterfaceFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->buttonMock = $this->getMockBuilder(ButtonInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Url::class,
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
        $productRenderInfoDto = $this->getMockForAbstractClass(ProductRenderInterface::class);
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
                    ActionInterface::PARAM_NAME_URL_ENCODED => "%uenc%"
                ]
            )
            ->willReturn(['some cart url post data']);

        $this->model->collect($product, $productRenderInfoDto);
    }
}
