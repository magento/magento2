<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\CustomerData;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Pricing\Render;
use Magento\Wishlist\Block\Customer\Sidebar;
use Magento\Wishlist\CustomerData\Wishlist;
use Magento\Wishlist\CustomerData\Wishlist as WishlistModel;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WishlistTest extends \PHPUnit_Framework_TestCase
{
    /** @var Wishlist */
    protected $model;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistHelperMock;

    /** @var Sidebar|\PHPUnit_Framework_MockObject_MockObject */
    protected $sidebarMock;

    /** @var Image|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogImageHelperMock;

    /** @var ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewMock;

    protected function setUp()
    {
        $this->wishlistHelperMock = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sidebarMock = $this->getMockBuilder('Magento\Wishlist\Block\Customer\Sidebar')
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\Framework\App\ViewInterface')
            ->getMockForAbstractClass();

        $this->catalogImageHelperMock = $this->getMockBuilder('Magento\Catalog\Helper\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $imageHelperFactory = $this->getMockBuilder('Magento\Catalog\Helper\ImageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $imageHelperFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->catalogImageHelperMock);

        $this->model = new Wishlist(
            $this->wishlistHelperMock,
            $this->sidebarMock,
            $imageHelperFactory,
            $this->viewMock
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
        $productUrl = 'product_url';
        $productName = 'product_name';
        $productPrice = 'product_price';
        $productIsSalable = true;
        $productIsVisible = true;
        $productHasOptions = false;
        $itemAddParams = ['add_params'];
        $itemRemoveParams = ['remove_params'];

        $result = [
            'counter' => __('1 item'),
            'items' => [
                [
                    'image' => [
                        'template' => 'Magento_Catalog/product/image',
                        'src' => $imageUrl,
                        'alt' => $imageLabel,
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                    ],
                    'product_url' => $productUrl,
                    'product_name' => $productName,
                    'product_price' => $productPrice,
                    'product_is_saleable_and_visible' => $productIsSalable && $productIsVisible,
                    'product_has_required_options' => $productHasOptions,
                    'add_to_cart_params' => $itemAddParams,
                    'delete_item_params' => $itemRemoveParams,
                ],
            ],
        ];

        /** @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemMock];

        $this->wishlistHelperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn(count($items));

        $this->viewMock->expects($this->once())
            ->method('loadLayout');

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Collection')
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

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getProduct')
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
        $this->catalogImageHelperMock->expects($this->once())
            ->method('getResizedImageInfo')
            ->willReturn([]);

        $this->wishlistHelperMock->expects($this->once())
            ->method('getProductUrl')
            ->with($itemMock, [])
            ->willReturn($productUrl);

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

        /** @var AbstractType|\PHPUnit_Framework_MockObject_MockObject $productTypeMock */
        $productTypeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Type\AbstractType')
            ->disableOriginalConstructor()
            ->setMethods(['hasRequiredOptions'])
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
            ->with($itemMock, true)
            ->willReturn($itemAddParams);
        $this->wishlistHelperMock->expects($this->once())
            ->method('getRemoveParams')
            ->with($itemMock, true)
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
        $productUrl = 'product_url';
        $productName = 'product_name';
        $productPrice = 'product_price';
        $productIsSalable = false;
        $productIsVisible = true;
        $productHasOptions = true;
        $itemAddParams = ['add_params'];
        $itemRemoveParams = ['remove_params'];

        /** @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemMock, $itemMock];

        $result = [
            'counter' =>  __('%1 items', count($items)),
            'items' => [
                [
                    'image' => [
                        'template' => 'Magento_Catalog/product/image',
                        'src' => $imageUrl,
                        'alt' => $imageLabel,
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                    ],
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
                        'template' => 'Magento_Catalog/product/image',
                        'src' => $imageUrl,
                        'alt' => $imageLabel,
                        'width' => $imageWidth,
                        'height' => $imageHeight,
                    ],
                    'product_url' => $productUrl,
                    'product_name' => $productName,
                    'product_price' => $productPrice,
                    'product_is_saleable_and_visible' => $productIsSalable && $productIsVisible,
                    'product_has_required_options' => $productHasOptions,
                    'add_to_cart_params' => $itemAddParams,
                    'delete_item_params' => $itemRemoveParams,
                ],
            ],
        ];

        $this->wishlistHelperMock->expects($this->once())
            ->method('getItemCount')
            ->willReturn(count($items));

        $this->viewMock->expects($this->once())
            ->method('loadLayout');

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $itemCollectionMock */
        $itemCollectionMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Collection')
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

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $productMock */
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->exactly(2))
            ->method('getProduct')
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
        $this->catalogImageHelperMock->expects($this->exactly(2))
            ->method('getResizedImageInfo')
            ->willReturn([]);

        $this->wishlistHelperMock->expects($this->exactly(2))
            ->method('getProductUrl')
            ->with($itemMock, [])
            ->willReturn($productUrl);

        $productMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn($productName);

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

        /** @var AbstractType|\PHPUnit_Framework_MockObject_MockObject $productTypeMock */
        $productTypeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Type\AbstractType')
            ->disableOriginalConstructor()
            ->setMethods(['hasRequiredOptions'])
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
            ->with($itemMock, true)
            ->willReturn($itemAddParams);
        $this->wishlistHelperMock->expects($this->exactly(2))
            ->method('getRemoveParams')
            ->with($itemMock, true)
            ->willReturn($itemRemoveParams);

        $this->assertEquals($result, $this->model->getSectionData());
    }

    public function testGetSectionDataWithoutItems()
    {
        $items = [];

        $result = [
            'counter' =>  null,
            'items' => [],
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
