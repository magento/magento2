<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Selection;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \Magento\Bundle\Model\Product\BundlePriceAbstract
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionFactory = Bootstrap::getObjectManager()->create(CollectionFactory::class);
    }

    /**
     * @magentoIndexerDimensionMode catalog_product_price website
     * @magentoDataFixture Magento/Bundle/_files/PriceCalculator/dynamic_bundle_product.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @group indexer_dimension
     * @dataProvider getTestCases
     */
    public function testAddPriceDataWithIndexerDimensionMode(array $strategy, int $expectedCount)
    {
        $this->prepareFixture($strategy, 'bundle_product');

        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('bundle_product', false, null, true);

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId(0);
        $collection->addPriceFilter($product, true);
        $items = $collection->getItems();

        $this->assertCount($expectedCount, $items);
    }

    public function getTestCases()
    {
        return [
            'Dynamic bundle product with three Simple products' => [
                'variation' => $this->getBundleConfiguration(),
                'expectedCount' => 1
            ]
        ];
    }

    private function getBundleConfiguration()
    {
        $optionsData = [
            [
                'title' => 'op1',
                'required' => true,
                'type' => 'checkbox',
                'links' => [
                    [
                        'sku' => 'simple1',
                        'qty' => 3,
                        'price' => 100,
                        'price_type' => 0,
                    ],
                    [
                        'sku' => 'simple2',
                        'qty' => 2,
                        'price' => 100,
                        'price_type' => 0,
                    ],
                    [
                        'sku' => 'simple3',
                        'qty' => 1,
                        'price' => 100,
                        'price_type' => 0,
                    ],
                ]
            ]
        ];

        return [
            [
                'modifierName' => 'addSimpleProduct',
                'data' => [$optionsData]
            ],
        ];
    }
}
