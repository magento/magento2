<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\TestFramework\Helper\Bootstrap;

class SpecialPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation disabled
     */
    public function testPriceInfoIfChildHasSpecialPrice()
    {
        $specialPrice = 2;

        /** @var Product $childProduct */
        $childProduct = $this->productRepository->get('simple_10', true);
        $childProduct->setData('special_price', $specialPrice);
        $this->productRepository->save($childProduct);

        /** @var Product $configurableProduct */
        $configurableProduct = $this->productRepository->get('configurable', true);
        $priceInfo = $configurableProduct->getPriceInfo();
        /** @var FinalPrice $finalPrice */
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);

        self::assertEquals($specialPrice, $finalPrice->getMinimalPrice()->getValue());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_simple_77.php
     * @magentoDbIsolation disabled
     */
    public function testSortingOfProductsIfChildHasNotSpecialPrice()
    {
        /** @var Product $simpleProduct */
        $simpleProduct = $this->productRepository->get('simple_77', true);
        $simpleProduct
            ->setOptions([])
            ->setTierPrice([])
            ->setPrice(5);
        $this->productRepository->save($simpleProduct);

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection
            ->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->setOrder(ProductInterface::PRICE, Collection::SORT_ORDER_DESC);

        /** @var Product[] $items */
        $items = array_values($collection->getItems());
        self::assertEquals('configurable', $items[0]->getSku());
        self::assertEquals('simple_77', $items[1]->getSku());
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_simple_77.php
     * @magentoDbIsolation disabled
     */
    public function testSortingOfProductsIfChildHasSpecialPrice()
    {
        /** @var Product $simpleProduct */
        $simpleProduct = $this->productRepository->get('simple_77', true);
        $simpleProduct
            ->setOptions([])
            ->setTierPrice([])
            ->setPrice(5);
        $this->productRepository->save($simpleProduct);

        /** @var Product $childProduct */
        $childProduct = $this->productRepository->get('simple_10', true);
        $childProduct->setData('special_price', 2);
        $this->productRepository->save($childProduct);

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection
            ->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
            ->setOrder(ProductInterface::PRICE, Collection::SORT_ORDER_DESC);

        /** @var Product[] $items */
        $items = array_values($collection->getItems());
        self::assertEquals('simple_77', $items[0]->getSku());
        self::assertEquals('configurable', $items[1]->getSku());
    }
}
