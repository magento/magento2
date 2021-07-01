<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\ProductList;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Block\Product\ProductList\Upsell as UpsellBlock;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductLinkCollection;
use Magento\Checkout\Model\ResourceModel\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UpsellTest extends TestCase
{
    const STUB_EMPTY_ARRAY = [];

    /**
     * @var UpsellBlock|MockObject
     */
    private $block;

    /**
     * @var CatalogConfig|MockObject
     */
    private $configMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Cart|MockObject
     */
    private $cartMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Visibility|MockObject
     */
    private $productVisibilityMock;

    /**
     * @var Session|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var Manager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManager;

    /**
     * @var ProductLinkCollection|MockObject
     */
    private $collectionMock;

    /**
     * @var MockObject
     */
    private $eventManagerMock;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(CatalogConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttributes'])
            ->getMock();
        $this->cartMock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUpSellProductCollection', 'getId', 'getTypeId'])
            ->getMock();
        $this->productVisibilityMock = $this->getMockBuilder(Visibility::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVisibleInCatalogIds'])
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(ProductLinkCollection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getUpSellProductCollection',
                'setPositionOrder',
                'addStoreFilter',
                'addMinimalPrice',
                'addFinalPrice',
                'addTaxPercents',
                'addAttributeToSelect',
                'addUrlRewrite',
                'setVisibility',
                'load'
            ])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->block = $this->objectManager->getObject(
            UpsellBlock::class,
            [
                'context' => $this->contextMock,
                'checkoutCart' => $this->cartMock,
                'catalogProductVisibility' => $this->productVisibilityMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'moduleManager' => $this->moduleManagerMock,
            ]
        );
    }

    /**
     * Tear Down.
     */
    protected function tearDown(): void
    {
        $this->block = null;
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetIdentities()
    {
        $productTag = ['compare_item_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')->willReturn($productTag);

        $itemsCollection = new \ReflectionProperty(UpsellBlock::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            $productTag,
            $this->block->getIdentities()
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetIdentitiesWhenItemGetIdentitiesReturnEmptyArray()
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->once())->method('getIdentities')
            ->willReturn(self::STUB_EMPTY_ARRAY);

        $itemsCollection = new \ReflectionProperty(UpsellBlock::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, [$product]);

        $this->assertEquals(
            self::STUB_EMPTY_ARRAY,
            $this->block->getIdentities()
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetIdentitiesWhenGetItemsReturnEmptyArray()
    {
        $itemsCollection = new \ReflectionProperty(UpsellBlock::class, '_items');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, self::STUB_EMPTY_ARRAY);

        $this->assertEquals(
            self::STUB_EMPTY_ARRAY,
            $this->block->getIdentities()
        );
    }

    /**
     * Test getItemCollection method.
     *
     * @throws \ReflectionException
     */
    public function testGetItemCollection()
    {
        $limit = ['upsell' => 4];
        $productAttributes = ['attribute1', 'attribute2'];

        $upsellProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['setDoNotUseCategoryId'])
            ->getMock();

        $upsellItems = [
            1 => $upsellProduct
        ];

        $upsellProduct->expects($this->once())
            ->method('setDoNotUseCategoryId')
            ->with(true);

        $this->productMock
            ->expects($this->once())
            ->method('getUpSellProductCollection')
            ->willReturn($this->collectionMock);

        $items = new \ReflectionProperty($this->collectionMock, '_items');
        $items->setAccessible(true);
        $items->setValue($this->collectionMock, $upsellItems);

        $limits = new \ReflectionProperty($this->block, '_itemLimits');
        $limits->setAccessible(true);
        $limits->setValue($this->block, $limit);

        $catalogConfig = new \ReflectionProperty($this->block, '_catalogConfig');
        $catalogConfig->setAccessible(true);
        $catalogConfig->setValue($this->block, $this->configMock);

        $eventManager = new \ReflectionProperty($this->block, '_eventManager');
        $eventManager->setAccessible(true);
        $eventManager->setValue($this->block, $this->eventManagerMock);

        $this->configMock
            ->expects($this->once())
            ->method('getProductAttributes')
            ->willReturn($productAttributes);

        $this->collectionMock
            ->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('addStoreFilter')
            ->willReturnSelf();

        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_Checkout')
            ->willReturn(true);

        $this->collectionMock
            ->expects($this->once())
            ->method('addMinimalPrice')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('addFinalPrice')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('addTaxPercents')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('addAttributeToSelect')
            ->with($productAttributes)
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('addUrlRewrite')
            ->willReturnSelf();

        $this->productVisibilityMock
            ->expects($this->once())
            ->method('getVisibleInCatalogIds');

        $this->collectionMock
            ->expects($this->once())
            ->method('setVisibility')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $this->eventManagerMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                'catalog_product_upsell',
                [
                    'product' => $this->productMock,
                    'collection' => $this->collectionMock,
                    'limit' => $this->block->getItemLimit()
                ]
            )
            ->willReturnSelf();

        $this->block->setData('product', $this->productMock);
        $this->block->getItemCollection();
    }
}
