<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\Checkout\Block\Cart\Crosssell;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CrosssellTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    private $checkoutSession;
    /**
     * @var Crosssell
     */
    private $model;
    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;
    /**
     * @var LinkFactory|MockObject
     */
    private $productLinkFactory;
    /**
     * @var array
     */
    private $productLinks = [];
    /**
     * @var Context
     */
    private $context;
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;
    /**
     * @var MockObject
     */
    private $productCollectionFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->storeManager = $this->createMock(
            StoreManagerInterface::class
        );
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'storeManager' => $this->storeManager
            ]
        );
        $this->checkoutSession = $this->createPartialMock(
            Session::class,
            [
                'getQuote',
                'getLastAddedProductId'
            ]
        );
        $this->productRepository = $this->createMock(
            ProductRepositoryInterface::class
        );
        $this->productLinkFactory = $this->createMock(
            LinkFactory::class
        );
        $this->productLinkFactory = $this->createMock(
            LinkFactory::class
        );
        $this->productCollectionFactory = $this->createMock(
            CollectionFactory::class
        );
        $this->model = $objectManager->getObject(
            Crosssell::class,
            [
                'context' => $this->context,
                'checkoutSession' => $this->checkoutSession,
                'productRepository' => $this->productRepository,
                'productLinkFactory' => $this->productLinkFactory,
                'productCollectionFactory' => $this->productCollectionFactory,
            ]
        );
    }

    /**
     * @dataProvider getItemsDataProvider
     * @param array $productLinks
     * @param array $cartProductIds
     * @param int|null $lastAddedProductId
     * @param array $expected
     */
    public function testGetItems(
        array $productLinks,
        array $cartProductIds,
        ?int $lastAddedProductId,
        array $expected
    ) {
        $this->productLinks = $productLinks;
        $cartProducts = array_map(
            function ($id) {
                return $this->getProduct(['entity_id' => $id]);
            },
            $cartProductIds
        );
        $cartItems = array_map(
            function ($product) {
                return new DataObject(['product' => $product]);
            },
            $cartProducts
        );
        $quote = new DataObject(['all_items' => $cartItems]);
        $this->checkoutSession->method('getQuote')
            ->willReturn($quote);
        $this->checkoutSession->method('getLastAddedProductId')
            ->willReturn($lastAddedProductId);
        $this->productRepository->method('getById')
            ->willReturnCallback(
                function ($id) {
                    return $this->getProduct(['entity_id' => $id]);
                }
            );
        $link = $this->createMock(Link::class);
        $this->productLinkFactory->method('create')
            ->willReturn($link);
        $link->method('useCrossSellLinks')
            ->willReturnSelf();
        $link->method('getProductCollection')
            ->willReturnCallback(
                function () {
                    return $this->createLinkCollection();
                }
            );
        $this->productCollectionFactory->method('create')
            ->willReturnCallback(
                function () {
                    return $this->createProductCollection();
                }
            );
        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManager->method('getStore')
            ->willReturn($store);
        $actual = array_map(
            function ($product) {
                return $product->getId();
            },
            $this->model->getItems()
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getItemsDataProvider(): array
    {
        $links = [
            1001 => [
                1003,
                1005
            ],
            1006 => [
                1002,
            ]
        ];
        return [
            [
                'productLinks' => $links,
                'cartProducts' => [
                    1001,
                    1006,
                ],
                'lastAddedProduct' => 1006,
                'cross-sells' => [
                    1002,
                    1003,
                    1005
                ]
            ],
            [
                'productLinks' => $links,
                'cartProducts' => [
                    1001,
                    1006,
                ],
                'lastAddedProduct' => null,
                'cross-sells' => [
                    1003,
                    1005,
                    1002,
                ]
            ],
            [
                'productLinks' => $links,
                'cartProducts' => [
                    1001,
                    1005,
                ],
                'lastAddedProduct' => null,
                'cross-sells' => [
                    1003
                ]
            ],
            [
                'productLinks' => $links,
                'cartProducts' => [
                    1002,
                    1003,
                ],
                'lastAddedProduct' => null,
                'cross-sells' => [
                ]
            ]
        ];
    }

    /**
     * @param array $data
     * @return MockObject
     */
    private function getProduct(array $data): MockObject
    {
        $product = $this->createPartialMock(Product::class, []);
        $product->setData($data);
        return $product;
    }

    /**
     * @return MockObject
     */
    private function createLinkCollection(): MockObject
    {
        $returnSelfMethods = [
            'setStoreId',
            'setPageSize',
            'setGroupBy',
            'setVisibility',
            'addMinimalPrice',
            'addFinalPrice',
            'addTaxPercents',
            'addAttributeToSelect',
            'addStoreFilter',
            'setPositionOrder',
            'addUrlRewrite',
            'load',
        ];
        $linkCollection = $this->createPartialMock(
            Collection::class,
            array_merge(
                $returnSelfMethods,
                [
                    'addProductFilter',
                    'addExcludeProductFilter',
                    'getSelect',
                    'getIterator',
                ]
            )
        );
        foreach ($returnSelfMethods as $method) {
            $linkCollection->method($method)
                ->willReturnSelf();
        }
        $linkCollection->method('addProductFilter')
            ->willReturnCallback(
                function ($products) use ($linkCollection) {
                    if (!is_array($products)) {
                        $products = [$products];
                    }
                    $linkCollection->setFlag('test_product_ids', $products);
                    return $linkCollection;
                }
            );
        $linkCollection->method('addExcludeProductFilter')
            ->willReturnCallback(
                function ($products) use ($linkCollection) {
                    if (!is_array($products)) {
                        $products = [$products];
                    }
                    $linkCollection->setFlag('test_exclude_product_ids', $products);
                    return $linkCollection;
                }
            );
        $linkCollection->method('getSelect')->willReturn(
            $this->createMock(Select::class)
        );
        $linkCollection->method('getIterator')
            ->willReturnCallback(
                function () use ($linkCollection) {
                    $productIds = $linkCollection->getFlag('test_product_ids') ?? [];
                    $excludeProductIds = $linkCollection->getFlag('test_exclude_product_ids') ?? [];
                    $links = [];
                    foreach ($productIds as $id) {
                        if (isset($this->productLinks[$id])) {
                            array_push($links, ...$this->productLinks[$id]);
                        }
                    }
                    $links = array_values(array_unique(array_diff($links, $excludeProductIds)));
                    $links = array_combine($links, $links);
                    $products = array_map(
                        function ($id) {
                            return $this->getProduct(['entity_id' => $id]);
                        },
                        $links
                    );
                    return new \ArrayIterator($products);
                }
            );
        return $linkCollection;
    }

    /**
     * @return MockObject
     */
    private function createProductCollection(): MockObject
    {
        $productCollection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $entityMetadataInterface =$this->getMockForAbstractClass(EntityMetadataInterface::class);
        $entityMetadataInterface->method('getLinkField')
            ->willReturn('entity_id');
        $productCollection->method('getProductEntityMetadata')
            ->willReturn($entityMetadataInterface);
        return $productCollection;
    }
}
