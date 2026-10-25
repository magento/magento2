<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
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

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $collectionFactory;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->registryMock = $this->createMock(Registry::class);
        $this->layerMock = $this->createMock(Layer::class);
        /** @var MockObject|Resolver $layerResolver */
        $layerResolver = $this->createPartialMock(Resolver::class, ['get', 'create']);
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->layerMock);
        $this->postDataHelperMock = $this->createMock(PostHelper::class);
        $this->typeInstanceMock = $this->createMock(Simple::class);
        $this->productMock = $this->createMock(Product::class);
        $this->cartHelperMock = $this->createMock(Cart::class);
        $this->catCollectionMock = $this->createMock(Collection::class);
        $this->prodCollectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->toolbarMock = $this->createMock(Toolbar::class);

        $this->urlHelperMock = $this->createMock(Data::class);
        $this->context = $this->createMock(Context::class);
        $this->renderer = $this->createMock(Render::class);
        $eventManager = $this->createMock(ManagerInterface::class);

        $this->context->expects($this->any())->method('getRegistry')->willReturn($this->registryMock);
        $this->context->expects($this->any())->method('getCartHelper')->willReturn($this->cartHelperMock);
        $this->context->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);
        $this->context->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createMock(StoreInterface::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $this->context->expects($this->any())->method('getStoreManager')->willReturn($storeManager);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->context->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfig);
        $inlineTranslation = $this->createMock(StateInterface::class);
        $this->context->expects($this->any())->method('getInlineTranslation')->willReturn($inlineTranslation);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);

        $this->block = $objectManager->getObject(
            ListProduct::class,
            [
                'registry' => $this->registryMock,
                'context' => $this->context,
                'layerResolver' => $layerResolver,
                'cartHelper' => $this->cartHelperMock,
                'postDataHelper' => $this->postDataHelperMock,
                'urlHelper' => $this->urlHelperMock,
                'collectionFactory' => $this->collectionFactory
            ]
        );
        $this->block->setToolbarBlockName('mock');
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testEmptyCollection(): void
    {
        $this->block->setData('translate_inline', true);
        $this->prodCollectionMock->expects($this->any())
            ->method('getItems')
            ->willThrowException(new \Exception('No items found.'));
        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($this->prodCollectionMock);
        $collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('addFieldToFilter');
        $currentCategory = $this->createMock(\Magento\Catalog\Model\Category::class);
        $currentCategory->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $this->layerMock->expects($this->any())
            ->method('getCurrentCategory')
            ->willReturn($currentCategory);
        $this->block->toHtml();
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
            ->willReturn(new \ArrayIterator([$currentCategory]));

        $this->prodCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->productMock]));

        $this->layerMock->expects($this->any())
            ->method('getCurrentCategory')
            ->willReturn($currentCategory);

        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($this->prodCollectionMock);

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
            ->with($this->productMock)
            ->willReturn(true);
        $this->cartHelperMock->expects($this->any())
            ->method('getAddUrl')
            ->with($this->productMock, ['_escape' => false])
            ->willReturn($url);
        $this->productMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($this->typeInstanceMock);
        $this->urlHelperMock->expects($this->once())
            ->method('getEncodedUrl')
            ->with($url)
            ->willReturn($uenc);
        $result = $this->block->getAddToCartPostParams($this->productMock);
        $this->assertEquals($expectedPostData, $result);
    }

    public function testSetIsProductListFlagOnGetProductPrice()
    {
        $this->renderer->expects($this->exactly(2))
            ->method('setData')
            ->willReturnSelf();
        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($this->renderer);
        $this->block->setCollection($this->prodCollectionMock);
        $this->block->getProductPrice($this->productMock);
    }
}
