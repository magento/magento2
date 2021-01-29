<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\LayoutInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Checkout\Helper\Cart|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var Data | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlHelperMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $catCollectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $prodCollectionMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $toolbarMock;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var Render|\PHPUnit\Framework\MockObject\MockObject
     */
    private $renderer;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->layerMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->layerMock);
        $this->postDataHelperMock = $this->createMock(\Magento\Framework\Data\Helper\PostHelper::class);
        $this->typeInstanceMock = $this->createMock(\Magento\Catalog\Model\Product\Type\Simple::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->cartHelperMock = $this->createMock(\Magento\Checkout\Helper\Cart::class);
        $this->catCollectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $this->prodCollectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->toolbarMock = $this->createMock(\Magento\Catalog\Block\Product\ProductList\Toolbar::class);

        $this->urlHelperMock = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->renderer = $this->getMockBuilder(Render::class)->disableOriginalConstructor()->getMock();
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class, [], '', false);

        $this->context->expects($this->any())->method('getRegistry')->willReturn($this->registryMock);
        $this->context->expects($this->any())->method('getCartHelper')->willReturn($this->cartHelperMock);
        $this->context->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($eventManager);

        $this->block = $objectManager->getObject(
            \Magento\Catalog\Block\Product\ListProduct::class,
            [
                'registry' => $this->registryMock,
                'context' => $this->context,
                'layerResolver' => $layerResolver,
                'cartHelper' => $this->cartHelperMock,
                'postDataHelper' => $this->postDataHelperMock,
                'urlHelper' => $this->urlHelperMock,
            ]
        );
        $this->block->setToolbarBlockName('mock');
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = 'cat_p_1';
        $categoryTag = 'cat_c_p_1';

        $this->productMock->expects($this->once())
            ->method('getIdentities')
            ->willReturn([$productTag]);

        $this->productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->willReturn($this->catCollectionMock);

        $this->catCollectionMock->expects($this->once())
            ->method('load')
            ->willReturn($this->catCollectionMock);

        $this->catCollectionMock->expects($this->once())
            ->method('setPage')
            ->willReturn($this->catCollectionMock);

        $this->catCollectionMock->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->willReturn($this->productMock);

        $currentCategory = $this->createMock(\Magento\Catalog\Model\Category::class);
        $currentCategory->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $this->catCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn([$currentCategory]);

        $this->prodCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productMock]));

        $this->layerMock->expects($this->any())
            ->method('getCurrentCategory')
            ->willReturn($currentCategory);

        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($this->prodCollectionMock);

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($this->toolbarMock);

        $this->assertEquals(
            [$categoryTag, $productTag],
            $this->block->getIdentities()
        );
        $this->assertEquals(
            '1',
            $this->block->getCategoryId()
        );
    }

    public function testGetAddToCartPostParams()
    {
        $url = 'http://localhost.com/dev/';
        $id = 1;
        $uenc = strtr(base64_encode($url), '+/=', '-_,');
        $expectedPostData = [
            'action' => $url,
            'data' => ['product' => $id, 'uenc' => $uenc],
        ];

        $this->typeInstanceMock->expects($this->once())
            ->method('isPossibleBuyFromList')
            ->with($this->equalTo($this->productMock))
            ->willReturn(true);
        $this->cartHelperMock->expects($this->any())
            ->method('getAddUrl')
            ->with($this->equalTo($this->productMock), $this->equalTo(['_escape' => false]))
            ->willReturn($url);
        $this->productMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstanceMock);
        $this->urlHelperMock->expects($this->once())
            ->method('getEncodedUrl')
            ->with($this->equalTo($url))
            ->willReturn($uenc);
        $result = $this->block->getAddToCartPostParams($this->productMock);
        $this->assertEquals($expectedPostData, $result);
    }

    public function testSetIsProductListFlagOnGetProductPrice()
    {
        $this->renderer->expects($this->once())
            ->method('setData')
            ->with('is_product_list', true)
            ->willReturnSelf();
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($this->renderer);

        $this->block->getProductPrice($this->productMock);
    }
}
