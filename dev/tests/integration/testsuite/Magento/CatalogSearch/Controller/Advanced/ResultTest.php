<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Controller\Advanced;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\TestFramework\TestCase\AbstractController;
use Laminas\Stdlib\Parameters;

/**
 * Test cases for catalog advanced search using search engine.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ResultTest extends AbstractController
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productAttributeRepository = $this->_objectManager->create(ProductAttributeRepositoryInterface::class);
    }

    /**
     * Advanced search test by difference product attributes.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @dataProvider searchStringDataProvider
     *
     * @param array $searchParams
     * @return void
     */
    public function testExecute(array $searchParams): void
    {
        if ('' !== $searchParams['test_searchable_attribute']) {
            $searchParams['test_searchable_attribute'] = $this->getAttributeOptionValueByOptionLabel(
                'test_searchable_attribute',
                $searchParams['test_searchable_attribute']
            );
        }

        $this->getRequest()->setQuery(
            $this->_objectManager->create(
                Parameters::class,
                [
                    'values' => $searchParams
                ]
            )
        );
        $this->dispatch('catalogsearch/advanced/result');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('Simple product name', $responseBody);
    }

    /**
     * Data provider with strings for quick search.
     *
     * @return array
     */
    public function searchStringDataProvider(): array
    {
        return [
            'search_product_by_name' => [
                [
                    'name' => 'Simple product name',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => '',
                        'to' => '',
                    ],
                    'test_searchable_attribute' => '',
                ],
            ],
            'search_product_by_sku' => [
                [
                    'name' => '',
                    'sku' => 'simple_for_search',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => '',
                        'to' => '',
                    ],
                    'test_searchable_attribute' => '',
                ],
            ],
            'search_product_by_description' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => 'Product description',
                    'short_description' => '',
                    'price' => [
                        'from' => '',
                        'to' => '',
                    ],
                    'test_searchable_attribute' => '',
                ],
            ],
            'search_product_by_short_description' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => 'Product short description',
                    'price' => [
                        'from' => '',
                        'to' => '',
                    ],
                    'test_searchable_attribute' => '',
                ],
            ],
            'search_product_by_price_range' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 50,
                        'to' => 150,
                    ],
                    'test_searchable_attribute' => '',
                ],
            ],
            'search_product_by_custom_attribute' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => '',
                        'to' => '',
                    ],
                    'test_searchable_attribute' => 'Option 1',
                ],
            ],
        ];
    }

    /**
     * Return attribute option value by option label.
     *
     * @param string $attributeCode
     * @param string $optionLabel
     * @return null|string
     */
    private function getAttributeOptionValueByOptionLabel(string $attributeCode, string $optionLabel): ?string
    {
        /** @var Attribute $attribute */
        $attribute = $this->productAttributeRepository->get($attributeCode);

        return $attribute->getSource()->getOptionId($optionLabel);
    }
}
