<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation disabled
 */
class AdvancedTest extends TestCase
{
    /**
     * @var Advanced
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(Advanced::class);
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/not_visible_searchable_product.php
     */
    public function testAddFilters(): void
    {
        $attributeCode = 'test_searchable_attribute';
        $attributeRepository = Bootstrap::getObjectManager()->get(ProductAttributeRepositoryInterface::class);
        $attribute = $attributeRepository->get($attributeCode);
        $option = $attribute->getOptions()[1];
        self::assertEquals('Option 1', $option->getLabel());

        $filters = [$attribute->getAttributeCode() => $option->getValue()];
        $this->model->addFilters($filters);
        $productCollection = $this->model->getProductCollection();
        $products = $productCollection->getItems();
        self::assertCount(1, $products);
        /** @var Product $product */
        $product = array_shift($products);
        self::assertEquals('simple_for_search', $product->getSku());
    }
}
