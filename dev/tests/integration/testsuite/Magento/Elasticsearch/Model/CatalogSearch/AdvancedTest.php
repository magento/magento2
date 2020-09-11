<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\CatalogSearch;

use Magento\CatalogSearch\Model\Advanced;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check catalog Advanced Search process with Elasticsearch enabled.
 */
class AdvancedTest extends TestCase
{
    /**
     * @var Visibility
     */
    private $productVisibility;

    /**
     * @var Advanced
     */
    private $advancedSearch;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->productVisibility = $objectManager->get(Visibility::class);
        $this->advancedSearch = $objectManager->get(Advanced::class);
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
        $searchName = 'Configurable';

        $this->advancedSearch->addFilters(['name' => $searchName]);
        /** @var ProductInterface[] $itemsResult */
        $itemsResult = $this->advancedSearch->getProductCollection()
            ->addAttributeToSelect(ProductInterface::VISIBILITY)
            ->getItems();
        $this->assertCount(1, $itemsResult);

        $product = array_shift($itemsResult);
        $this->assertStringContainsString($searchName, $product->getName());
        $this->assertContains((int)$product->getVisibility(), $this->productVisibility->getVisibleInSearchIds());
    }
}
