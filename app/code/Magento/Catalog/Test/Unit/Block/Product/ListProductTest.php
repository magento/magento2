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
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProductTest extends TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Checkout\Helper\Cart|MockObject
     */
    protected $cartHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var Data|MockObject
     */
    protected $urlHelperMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category|MockObject
     */
    protected $catCollectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|MockObject
     */
    protected $prodCollectionMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface | MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar|MockObject
     */
    protected $toolbarMock;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Render|MockObject
     */
    private $renderer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->layerMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
        /** @var MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($this->layerMock));
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

    protected function tearDown()
    {
        $this->block = null;
    }

    /**
     * Verify identitioes
     *
     * @return void
     */
    public function testGetIdentities(): void
    {
        $productTag = 'cat_p_1';
        $categoryTag = 'cat_c_p_1';

        $this->productMock->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue([$productTag]));

        $this->productMock->expects($this->once())
            ->method('getCategoryCollection')
            ->will($this->returnValue($this->catCollectionMock));

        $this->catCollectionMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->catCollectionMock));

        $this->catCollectionMock->expects($this->once())
            ->method('setPage')
            ->will($this->returnValue($this->catCollectionMock));

        $this->catCollectionMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $this->registryMock->expects($this->any())
            ->method('registry')
            ->will($this->returnValue($this->productMock));

        $currentCategory = $this->createMock(\Magento\Catalog\Model\Category::class);
        $currentCategory->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('1'));

        $this->catCollectionMock->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue([$currentCategory]));

        $this->prodCollectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->productMock])));

        $this->layerMock->expects($this->any())
            ->method('getCurrentCategory')
            ->will($this->returnValue($currentCategory));

        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->will($this->returnValue($this->prodCollectionMock));

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->toolbarMock));

        $this->assertEquals(
            [$categoryTag, $productTag],
            $this->block->getIdentities()
        );
        $this->assertEquals(
            '1',
            $this->block->getCategoryId()
        );
    }

    /**
     * Verify addToolbarBlock not ovveride product collection
     *
     * @return void
     */
    public function testAddToolbarBlockCollection(): void
    {
        $currentCategory = $this->createMock(\Magento\Catalog\Model\Category::class);
        $currentCategory->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('1'));
        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($this->prodCollectionMock);
        $this->layerMock->expects($this->any())
            ->method('getCurrentCategory')
            ->will($this->returnValue($currentCategory));
        $this->layoutMock->expects($this->exactly(2))
            ->method('getBlock')
            ->will($this->returnValue($this->toolbarMock));
        $this->toolbarMock->expects($this->at(3))
            ->method('getCollection')
            ->willReturn($this->prodCollectionMock);
        $this->toolbarMock->expects($this->once())
            ->method('setCollection')
            ->with($this->prodCollectionMock);

        $this->block->getLoadedProductCollection();
        $addToolbarBlock = $this->getMethod('addToolbarBlock');
        $addToolbarBlock->invokeArgs($this->block, [$this->prodCollectionMock]);
    }

    /**
     * Access protected method
     *
     * @param string $name
     */
    private function getMethod(string $name)
    {
        $class = new \ReflectionClass(\Magento\Catalog\Block\Product\ListProduct::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
    /**
     * Verify add to cart post params
     *
     * @return void
     */
    public function testGetAddToCartPostParams(): void
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
            ->will($this->returnValue(true));
        $this->cartHelperMock->expects($this->any())
            ->method('getAddUrl')
            ->with($this->equalTo($this->productMock), $this->equalTo([]))
            ->will($this->returnValue($url));
        $this->productMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue($id));
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($this->typeInstanceMock));
        $this->urlHelperMock->expects($this->once())
            ->method('getEncodedUrl')
            ->with($this->equalTo($url))
            ->will($this->returnValue($uenc));
        $result = $this->block->getAddToCartPostParams($this->productMock);
        $this->assertEquals($expectedPostData, $result);
    }

    /**
     * Verify ListFlag on get product price
     *
     * @return void
     */
    public function testSetIsProductListFlagOnGetProductPrice(): void
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
