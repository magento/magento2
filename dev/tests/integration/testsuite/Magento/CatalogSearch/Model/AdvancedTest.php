<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Search\Request\Config as RequestConfig;
use Magento\Framework\Search\Request\Config\FilesystemReader as RequestConfigReader;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AdvancedTest extends TestCase
{
    /**
     * @var Advanced
     */
    private $model;

    protected function setUp(): void
    {
        $requestConfigReader = Bootstrap::getObjectManager()->get(RequestConfigReader::class);
        $requestConfig = Bootstrap::getObjectManager()->get(RequestConfig::class);
        $requestConfig->merge($requestConfigReader->read());

        $this->model = Bootstrap::getObjectManager()->create(Advanced::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/products_with_not_empty_layered_navigation_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/not_visible_product_with_layered_navigation_attribute.php
     */
    public function testAddFilters(): void
    {
        $attributeCode = 'test_configurable';
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
        self::assertEquals('Option 1', $product->getAttributeText($attributeCode));
    }
}
