<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProductTest extends TestCase
{
    /**
     * @var ListProduct
     */
    protected $block;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $layerMock;

    /**
     * @var PostHelper|MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Cart|MockObject
     */
    protected $cartHelperMock;

    /**
     * @var Simple|MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var Data|MockObject
     */
    protected $urlHelperMock;

    /**
     * @var CategoryResourceModel|MockObject
     */
    protected $catCollectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product|MockObject
     */
    protected $prodCollectionMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var Toolbar|MockObject
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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->registryMock = $this->createMock(Registry::class);
        $this->layerMock = $this->createMock(Layer::class);
        /** @var MockObject|Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($this->layerMock));
        $this->postDataHelperMock = $this->createMock(PostHelper::class);
        $this->typeInstanceMock = $this->createMock(Simple::class);
        $this->productMock = $this->createMock(Product::class);
        $this->cartHelperMock = $this->createMock(Cart::class);
        $this->catCollectionMock = $this->createMock(Collection::class);
        $this->prodCollectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->toolbarMock = $this->createMock(Toolbar::class);

        $this->urlHelperMock = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->getMock();
        $this->context = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->renderer = $this->getMockBuilder(Render::class)->disableOriginalConstructor()->getMock();
        $eventManager = $this->getMockForAbstractClass(ManagerInterface::class, [], '', false);

        $this->context->expects($this->any())->method('getRegistry')->willReturn($this->registryMock);
        $this->context->expects($this->any())->method('getCartHelper')->willReturn($this->cartHelperMock);
        $this->context->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($eventManager);

        $this->block = $objectManager->getObject(
            ListProduct::class,
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
