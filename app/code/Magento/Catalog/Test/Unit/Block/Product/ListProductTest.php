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
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Checkout\Helper\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlHelperMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catCollectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $prodCollectionMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $toolbarMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var Render|\PHPUnit_Framework_MockObject_MockObject
     */
    private $renderer;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->layerMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
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

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->will($this->returnValue($this->toolbarMock));

        $this->assertEquals(
            [$productTag, $categoryTag],
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
