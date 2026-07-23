<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
        $this->abstractProductMock = $this->createMock(AbstractProduct::class);
        $this->catalogProductHelperMock = $this->createMock(Compare::class);
        $this->postHelperMock = $this->createMock(PostHelper::class);

        $this->buttonFactoryMock = $this->createPartialMock(
            ButtonInterfaceFactory::class,
            ['create']
        );

        $this->buttonMock = $this->createMock(ButtonInterface::class);

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

    public function testCollectWithNullButtons()
    {
        $product = $this->createMock(Product::class);
      
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);

        // Mock both getAddToCartButton and getAddToCompareButton returning null (line 81-82)
        $productRenderInfoDto->expects($this->once())
            ->method('getAddToCartButton')
            ->willReturn(null);
        $productRenderInfoDto->expects($this->once())
            ->method('getAddToCompareButton')
            ->willReturn(null);

        $this->catalogProductHelperMock
            ->expects($this->once())
            ->method('getPostDataParams')
            ->with($product)
            ->willReturn(['Some compare Data']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $product->expects($this->once())
            ->method('getData')
            ->with('has_options')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getProductUrl')
            ->willReturn('http://example.com/product/1');

        // Expect buttonFactory to be called twice (once for cart, once for compare) since both are null
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

        // Verify buttons are set back to productRender
        $productRenderInfoDto->expects($this->once())
            ->method('setAddToCartButton')
            ->with($this->buttonMock);
        $productRenderInfoDto->expects($this->once())
            ->method('setAddToCompareButton')
            ->with($this->buttonMock);
        $productRenderInfoDto->expects($this->once())
            ->method('setUrl')
            ->with('http://example.com/product/1');

        $this->model->collect($product, $productRenderInfoDto);
    }

    public function testCollectWithExistingAddToCompareButton()
    {
        $product = $this->createMock(Product::class);
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);

        $existingCompareButton = $this->createMock(ButtonInterface::class);

        // Test line 82: getAddToCompareButton returns existing button
        $productRenderInfoDto->expects($this->once())
            ->method('getAddToCartButton')
            ->willReturn(null);
        $productRenderInfoDto->expects($this->once())
            ->method('getAddToCompareButton')
            ->willReturn($existingCompareButton);

        $this->catalogProductHelperMock
            ->expects($this->once())
            ->method('getPostDataParams')
            ->with($product)
            ->willReturn(['Some compare Data']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $product->expects($this->once())
            ->method('getData')
            ->with('has_options')
            ->willReturn(false);
        $product->expects($this->once())
            ->method('getProductUrl')
            ->willReturn('http://example.com/product/1');

        // Expect buttonFactory to be called only once (for cart button) since compare button exists
        $this->buttonFactoryMock->expects($this->once())
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

        // Verify the existing compare button is used and configured
        $existingCompareButton->expects($this->once())
            ->method('setUrl')
            ->with(['Some compare Data']);

        // Verify buttons are set back to productRender
        $productRenderInfoDto->expects($this->once())
            ->method('setAddToCartButton')
            ->with($this->buttonMock);
        $productRenderInfoDto->expects($this->once())
            ->method('setAddToCompareButton')
            ->with($existingCompareButton);
        $productRenderInfoDto->expects($this->once())
            ->method('setUrl')
            ->with('http://example.com/product/1');

        $this->model->collect($product, $productRenderInfoDto);
    }

    public function testCollectWithExistingButtons()
    {
        $product = $this->createMock(Product::class);
        $productRenderInfoDto = $this->createMock(ProductRenderInterface::class);

        $existingCartButton = $this->createMock(ButtonInterface::class);
        $existingCompareButton = $this->createMock(ButtonInterface::class);

        // Test both buttons already exist
        $productRenderInfoDto->expects($this->once())
            ->method('getAddToCartButton')
            ->willReturn($existingCartButton);
        $productRenderInfoDto->expects($this->once())
            ->method('getAddToCompareButton')
            ->willReturn($existingCompareButton);

        $this->catalogProductHelperMock
            ->expects($this->once())
            ->method('getPostDataParams')
            ->with($product)
            ->willReturn(['Some compare Data']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $product->expects($this->once())
            ->method('getData')
            ->with('has_options')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getProductUrl')
            ->willReturn('http://example.com/product/1');

        // Expect buttonFactory to NOT be called since both buttons exist
        $this->buttonFactoryMock->expects($this->never())
            ->method('create');
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

        // Verify both existing buttons are used and configured
        $existingCartButton->expects($this->once())
            ->method('setPostData')
            ->with(['some cart url post data']);
        $existingCartButton->expects($this->once())
            ->method('setRequiredOptions')
            ->with(true);
        $existingCartButton->expects($this->once())
            ->method('setUrl')
            ->with('some:url');
        $existingCompareButton->expects($this->once())
            ->method('setUrl')
            ->with(['Some compare Data']);

        // Verify buttons are set back to productRender
        $productRenderInfoDto->expects($this->once())
            ->method('setAddToCartButton')
            ->with($existingCartButton);
        $productRenderInfoDto->expects($this->once())
            ->method('setAddToCompareButton')
            ->with($existingCompareButton);
        $productRenderInfoDto->expects($this->once())
            ->method('setUrl')
            ->with('http://example.com/product/1');

        $this->model->collect($product, $productRenderInfoDto);
    }
}
