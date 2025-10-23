<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\CustomerData;

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Pricing\Render;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Block\Customer\Sidebar;
use Magento\Wishlist\CustomerData\Wishlist;
use Magento\Wishlist\CustomerData\Wishlist as WishlistModel;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WishlistTest extends TestCase
{
    /** @var Wishlist */
    private $model;

    /** @var Data|MockObject */
    private $wishlistHelperMock;

    /** @var Sidebar|MockObject */
    private $sidebarMock;

    /** @var Image|MockObject */
    private $catalogImageHelperMock;

    /** @var ViewInterface|MockObject */
    private $viewMock;

    /** @var ImageBuilder|MockObject */
    private $itemResolver;

    /** @var StoreManagerInterface|MockObject */
    private $storeManagerMock;

    /** @var WebsiteInterface|MockObject */
    private $websiteMock;

    /** @var StoreInterface|MockObject */
    private $storeMock;

    /** @var ImageFactory|MockObject */
    private $imageHelperFactory;

    protected function setUp(): void
    {
        $this->wishlistHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sidebarMock = $this->getMockBuilder(Sidebar::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(ViewInterface::class)
            ->getMockForAbstractClass();

        $this->catalogImageHelperMock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageHelperFactory = $this->getMockBuilder(ImageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->imageHelperFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->catalogImageHelperMock);

        $this->itemResolver = $this->createMock(
            ItemResolverInterface::class
        );

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->onlyMethods(['getId',])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(['getId',])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new Wishlist(
            $this->wishlistHelperMock,
            $this->sidebarMock,
            $this->imageHelperFactory,
            $this->viewMock,
            $this->itemResolver,
            $this->storeManagerMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetSectionData()
    {
        $imageUrl = 'image_url';
        $imageLabel = 'image_label';
        $imageWidth = 'image_width';
        $imageHeight = 'image_height';
        $productSku = 'product_sku';
        $productId = 'product_id';
        $productUrl = 'product_url';
        $productName = 'product_name';
        $productPrice = 'product_price';
        $productIsSalable = true;
        $productIsVisible = true;
        $productHasOptions = false;
        $itemAddParams = ['add_params'];
        $itemRemoveParams = ['remove_params'];

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $result = [
            'counter' => __('1 item'),
            'items' => [
                [
                    'image' => [
                        'template' => 'Magento_Catalog/product/image_with_borders',
                        'src' => $imageUrl,
                        'alt' => $imageLabel,
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                    ],
                    'product_sku' => $productSku,
                    'product_id' => $productId,
                    'product_url' => $productUrl,
                    'product_name' => $productName,
                    'product_price' => $productPrice,
                    'product_is_saleable_and_visible' => $productIsSalable && $productIsVisible,
                    'product_has_required_options' => $productHasOptions,
                    'add_to_cart_params' => $itemAddParams,
                    'delete_item_params' => $itemRemoveParams,
                ],
            ],
            'websiteId' => 1,
            'storeId' => 1
        ];

        /** @var Item|MockObject $itemMock */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemMock];

        $this->wishlistHelperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn(count($items));

        $this->viewMock->expects($this->once())
            ->method('loadLayout');

        /** @var Collection|MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistHelperMock->expects($this->once())
            ->method('getWishlistItemCollection')
            ->willReturn($itemCollectionMock);

        $itemCollectionMock->expects($this->once())
            ->method('clear')
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('setPageSize')
            ->with(WishlistModel::SIDEBAR_ITEMS_NUMBER)
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('setInStockFilter')
            ->with(true)
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('setOrder')
            ->with('added_at')
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        /** @var Product|MockObject $productMock */
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $this->itemResolver->expects($this->once())
            ->method('getFinalProduct')
            ->willReturn($productMock);

        $this->catalogImageHelperMock->expects($this->once())
            ->method('init')
            ->with($productMock, 'wishlist_sidebar_block', [])
            ->willReturnSelf();
        $this->catalogImageHelperMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($imageUrl);
        $this->catalogImageHelperMock->expects($this->once())
            ->method('getLabel')
            ->willReturn($imageLabel);
        $this->catalogImageHelperMock->expects($this->once())
            ->method('getWidth')
            ->willReturn($imageWidth);
        $this->catalogImageHelperMock->expects($this->once())
            ->method('getHeight')
            ->willReturn($imageHeight);
        $this->catalogImageHelperMock->expects($this->any())
            ->method('getFrame')
            ->willReturn(true);

        $this->wishlistHelperMock->expects($this->once())
            ->method('getProductUrl')
            ->with($itemMock, [])
            ->willReturn($productUrl);

        $productMock->expects($this->once())
            ->method('getSku')
            ->willReturn($productSku);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($productName);

        $this->sidebarMock->expects($this->once())
            ->method('getProductPriceHtml')
            ->with(
                $productMock,
                'wishlist_configured_price',
                Render::ZONE_ITEM_LIST,
                ['item' => $itemMock]
            )
            ->willReturn($productPrice);

        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($productName);
        $productMock->expects($this->once())
            ->method('isSaleable')
            ->willReturn($productIsSalable);
        $productMock->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn($productIsVisible);

        /** @var AbstractType|MockObject $productTypeMock */
        $productTypeMock = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRequiredOptions'])
            ->getMockForAbstractClass();

        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);

        $productTypeMock->expects($this->once())
            ->method('hasRequiredOptions')
            ->with($productMock)
            ->willReturn($productHasOptions);

        $this->wishlistHelperMock->expects($this->once())
            ->method('getAddToCartParams')
            ->with($itemMock)
            ->willReturn($itemAddParams);
        $this->wishlistHelperMock->expects($this->once())
            ->method('getRemoveParams')
            ->with($itemMock)
            ->willReturn($itemRemoveParams);

        $this->assertEquals($result, $this->model->getSectionData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetSectionDataWithTwoItems()
    {
        $imageUrl = 'image_url';
        $imageLabel = 'image_label';
        $imageWidth = 'image_width';
        $imageHeight = 'image_height';
        $productSku = 'product_sku';
        $productId = 'product_id';
        $productUrl = 'product_url';
        $productName = 'product_name';
        $productPrice = 'product_price';
        $productIsSalable = false;
        $productIsVisible = true;
        $productHasOptions = true;
        $itemAddParams = ['add_params'];
        $itemRemoveParams = ['remove_params'];

        /** @var Item|MockObject $itemMock */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemMock, $itemMock];

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $result = [
            'counter' =>  __('%1 items', count($items)),
            'items' => [
                [
                    'image' => [
                        'template' => 'Magento_Catalog/product/image_with_borders',
                        'src' => $imageUrl,
                        'alt' => $imageLabel,
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                    ],
                    'product_sku' => $productSku,
                    'product_id' => $productId,
                    'product_url' => $productUrl,
                    'product_name' => $productName,
                    'product_price' => $productPrice,
                    'product_is_saleable_and_visible' => $productIsSalable && $productIsVisible,
                    'product_has_required_options' => $productHasOptions,
                    'add_to_cart_params' => $itemAddParams,
                    'delete_item_params' => $itemRemoveParams,
                ],
                [
                    'image' => [
                        'template' => 'Magento_Catalog/product/image_with_borders',
                        'src' => $imageUrl,
                        'alt' => $imageLabel,
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                    ],
                    'product_sku' => $productSku,
                    'product_id' => $productId,
                    'product_url' => $productUrl,
                    'product_name' => $productName,
                    'product_price' => $productPrice,
                    'product_is_saleable_and_visible' => $productIsSalable && $productIsVisible,
                    'product_has_required_options' => $productHasOptions,
                    'add_to_cart_params' => $itemAddParams,
                    'delete_item_params' => $itemRemoveParams,
                ],
            ],
            'websiteId' => 1,
            'storeId' => 1
        ];

        $this->wishlistHelperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn(count($items));

        $this->viewMock->expects($this->once())
            ->method('loadLayout');

        /** @var Collection|MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistHelperMock->expects($this->once())
            ->method('getWishlistItemCollection')
            ->willReturn($itemCollectionMock);

        $itemCollectionMock->expects($this->once())
            ->method('clear')
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('setPageSize')
            ->with(WishlistModel::SIDEBAR_ITEMS_NUMBER)
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('setInStockFilter')
            ->with(true)
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('setOrder')
            ->with('added_at')
            ->willReturnSelf();
        $itemCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        /** @var Product|MockObject $productMock */
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($productMock);

        $this->itemResolver->expects($this->exactly(2))
            ->method('getFinalProduct')
            ->willReturn($productMock);

        $this->catalogImageHelperMock->expects($this->exactly(2))
            ->method('init')
            ->with($productMock, 'wishlist_sidebar_block', [])
            ->willReturnSelf();
        $this->catalogImageHelperMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn($imageUrl);
        $this->catalogImageHelperMock->expects($this->exactly(2))
            ->method('getLabel')
            ->willReturn($imageLabel);
        $this->catalogImageHelperMock->expects($this->exactly(2))
            ->method('getWidth')
            ->willReturn($imageWidth);
        $this->catalogImageHelperMock->expects($this->exactly(2))
            ->method('getHeight')
            ->willReturn($imageHeight);
        $this->catalogImageHelperMock->expects($this->any())
            ->method('getFrame')
            ->willReturn(true);

        $this->wishlistHelperMock->expects($this->exactly(2))
            ->method('getProductUrl')
            ->with($itemMock, [])
            ->willReturn($productUrl);

        $productMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($productName);

        $productMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($productId);

        $productMock->expects($this->exactly(2))
            ->method('getSku')
            ->willReturn($productSku);

        $this->sidebarMock->expects($this->exactly(2))
            ->method('getProductPriceHtml')
            ->with(
                $productMock,
                'wishlist_configured_price',
                Render::ZONE_ITEM_LIST,
                ['item' => $itemMock]
            )
            ->willReturn($productPrice);

        $productMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($productName);
        $productMock->expects($this->exactly(2))
            ->method('isSaleable')
            ->willReturn($productIsSalable);
        $productMock->expects($this->never())
            ->method('isVisibleInSiteVisibility');

        /** @var AbstractType|MockObject $productTypeMock */
        $productTypeMock = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRequiredOptions'])
            ->getMockForAbstractClass();

        $productMock->expects($this->exactly(2))
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);

        $productTypeMock->expects($this->exactly(2))
            ->method('hasRequiredOptions')
            ->with($productMock)
            ->willReturn($productHasOptions);

        $this->wishlistHelperMock->expects($this->exactly(2))
            ->method('getAddToCartParams')
            ->with($itemMock)
            ->willReturn($itemAddParams);
        $this->wishlistHelperMock->expects($this->exactly(2))
            ->method('getRemoveParams')
            ->with($itemMock)
            ->willReturn($itemRemoveParams);

        $this->assertEquals($result, $this->model->getSectionData());
    }

    public function testGetSectionDataWithoutItems()
    {
        $items = [];

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $result = [
            'counter' =>  null,
            'items' => [],
            'websiteId' =>null,
            'storeId' => null
        ];

        $this->wishlistHelperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn(count($items));

        $this->viewMock->expects($this->never())
            ->method('loadLayout');

        $this->wishlistHelperMock->expects($this->never())
            ->method('getWishlistItemCollection');

        $this->catalogImageHelperMock->expects($this->never())
            ->method('init');
        $this->catalogImageHelperMock->expects($this->never())
            ->method('getUrl');
        $this->catalogImageHelperMock->expects($this->never())
            ->method('getLabel');
        $this->catalogImageHelperMock->expects($this->never())
            ->method('getWidth');
        $this->catalogImageHelperMock->expects($this->never())
            ->method('getHeight');

        $this->wishlistHelperMock->expects($this->never())
            ->method('getProductUrl');

        $this->sidebarMock->expects($this->never())
            ->method('getProductPriceHtml');

        $this->wishlistHelperMock->expects($this->never())
            ->method('getAddToCartParams');
        $this->wishlistHelperMock->expects($this->never())
            ->method('getRemoveParams');

        $this->assertEquals($result, $this->model->getSectionData());
    }
}
