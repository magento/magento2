<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\CatalogSearch;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\Advanced;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check catalog Advanced Search process with Elasticsearch enabled.
 */
class AdvancedTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Visibility
     */
    private $productVisibility;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productVisibility = $this->objectManager->get(Visibility::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Check that Advanced Search does NOT return products that do NOT have search visibility.
     *
     * @magentoDbIsolation disabled
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @return void
     */
    public function testAddFilters(): void
    {
        $this->assertResultsAfterRequest(1);

        /** @var ProductInterface $configurableProductOption */
        $configurableProductOption = $this->productRepository->get('Simple option 1');
        $configurableProductOption->setVisibility(Visibility::VISIBILITY_IN_SEARCH);
        $this->productRepository->save($configurableProductOption);

        $this->registry->unregister('advanced_search_conditions');
        $this->assertResultsAfterRequest(2);
    }

    /**
     * Do Elasticsearch query and assert results.
     *
     * @param int $count
     * @return void
     */
    private function assertResultsAfterRequest(int $count): void
    {
        /** @var Advanced $advancedSearch */
        $advancedSearch = $this->objectManager->create(Advanced::class);
        $advancedSearch->addFilters(['name' => 'Configurable']);

        /** @var ProductInterface[] $itemsResult */
        $itemsResult = $advancedSearch->getProductCollection()
            ->addAttributeToSelect(ProductInterface::VISIBILITY)
            ->getItems();

        $this->assertCount($count, $itemsResult);
        foreach ($itemsResult as $product) {
            $this->assertStringContainsString('Configurable', $product->getName());
            $this->assertContains((int)$product->getVisibility(), $this->productVisibility->getVisibleInSearchIds());
        }
    }
}
