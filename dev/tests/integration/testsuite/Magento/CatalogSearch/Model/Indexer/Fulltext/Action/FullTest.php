<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogSearch\Model\ResourceModel\Engine;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

class FullTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     */
    protected $actionFull;

    protected function setUp()
    {
        $this->actionFull = Bootstrap::getObjectManager()->create(
            \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::class
        );
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_index.php
     * @magentoDataFixture Magento/CatalogSearch/_files/product_configurable_not_available.php
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable.php
     */
    public function testGetIndexData()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $allowedStatuses = Bootstrap::getObjectManager()->get(Status::class)->getVisibleStatusIds();
        $allowedVisibility = Bootstrap::getObjectManager()->get(Engine::class)->getAllowedVisibility();

        $result = iterator_to_array($this->actionFull->rebuildStoreIndex(Store::DISTRO_STORE_ID));
        $this->assertNotEmpty($result);

        $productsIds = array_keys($result);
        foreach ($productsIds as $productId) {
            $product = $productRepository->getById($productId);
            $this->assertContains($product->getVisibility(), $allowedVisibility);
            $this->assertContains($product->getStatus(), $allowedStatuses);
        }

        $expectedData = $this->getExpectedIndexData();
        foreach ($expectedData as $sku => $expectedIndexData) {
            $product = $productRepository->get($sku);
            $this->assertEquals($expectedIndexData, $result[$product->getId()]);
        }
    }

    /**
     * @return array
     */
    private function getExpectedIndexData()
    {
        /** @var ProductAttributeRepositoryInterface $attributeRepository */
        $attributeRepository = Bootstrap::getObjectManager()->get(ProductAttributeRepositoryInterface::class);
        $skuId = $attributeRepository->get(ProductInterface::SKU)->getAttributeId();
        $nameId = $attributeRepository->get(ProductInterface::NAME)->getAttributeId();
        /** @see dev/tests/integration/testsuite/Magento/Framework/Search/_files/configurable_attribute.php */
        $configurableId = $attributeRepository->get('test_configurable_searchable')->getAttributeId();
        return [
            'configurable_searchable' => [
                $skuId => 'configurable_searchable',
                $configurableId => 'Option 1 | Option 2',
                $nameId => 'Configurable Product | Configurable OptionOption 1 | Configurable OptionOption 2',
            ],
            'index_enabled' => [
                $skuId => 'index_enabled',
                $nameId => 'index enabled',
            ],
            'index_visible_search' => [
                $skuId => 'index_visible_search',
                $nameId => 'index visible search',
            ],
            'index_visible_category' => [
                $skuId => 'index_visible_category',
                $nameId => 'index visible category',
            ],
            'index_visible_both' => [
                $skuId => 'index_visible_both',
                $nameId => 'index visible both',
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testRebuildStoreIndexConfigurable()
    {
        $storeId = 1;

        $simpleProductId = $this->getIdBySku('simple_10');
        $configProductId = $this->getIdBySku('configurable');

        $expected = [
            $simpleProductId,
            $configProductId
        ];
        $storeIndexDataSimple = $this->actionFull->rebuildStoreIndex($storeId, [$simpleProductId]);
        $storeIndexDataExpected = $this->actionFull->rebuildStoreIndex($storeId, $expected);

        $this->assertEquals($storeIndexDataSimple, $storeIndexDataExpected);
    }

    /**
     * @param string $sku
     * @return int
     */
    private function getIdBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(Product::class);

        return $product->getIdBySku($sku);
    }
}
