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
        $this->assertStringNotContainsString('Not visible simple product', $responseBody);
    }

    /**
     * Advanced search test by difference product attributes.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search_with_hyphen_in_sku.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     *
     * @return void
     */
    public function testExecuteSkuWithHyphen(): void
    {
        $this->getRequest()->setQuery(
            $this->_objectManager->create(
                Parameters::class,
                [
                    'values' => [
                        'name' => '',
                        'sku' => '24-mb01',
                        'description' => '',
                        'short_description' => '',
                        'price' => [
                            'from' => '',
                            'to' => '',
                        ],
                        'test_searchable_attribute' => '',
                    ]
                ]
            )
        );
        $this->dispatch('catalogsearch/advanced/result');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('Simple product name', $responseBody);
    }

    /**
     * Advanced search with an underscore in product attributes.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search_with_underscore.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     *
     * @return void
     */
    public function testExecuteWithUnderscore(): void
    {
        $this->getRequest()->setQuery(
            $this->_objectManager->create(
                Parameters::class,
                [
                    'values' => [
                        'name' => 'name',
                        'sku' => 'sku',
                        'description' => 'description',
                        'short_description' => 'short',
                        'price' => [
                            'from' => '',
                            'to' => '',
                        ],
                    ],
                ]
            )
        );
        $this->dispatch('catalogsearch/advanced/result');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('name_simple_product', $responseBody);
    }

    /**
     * Advanced search with array in price parameters
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @dataProvider searchParamsInArrayDataProvider
     *
     * @param array $searchParams
     * @return void
     */
    public function testExecuteWithArrayInParam(array $searchParams): void
    {
        $this->getRequest()->setQuery(
            $this->_objectManager->create(
                Parameters::class,
                [
                    'values' => $searchParams
                ]
            )
        );
        $this->dispatch('catalogsearch/advanced/result');
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString(
            'We can&#039;t find any items matching these search criteria.',
            $responseBody
        );
    }

    /**
     * Advanced search test by difference product attributes.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @dataProvider testDataForAttributesCombination
     *
     * @param array $searchParams
     * @param bool $isProductShown
     * @return void
     */
    public function testExecuteForAttributesCombination(array $searchParams, bool $isProductShown): void
    {
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

        if ($isProductShown) {
            $this->assertStringContainsString('Simple product name', $responseBody);
        } else {
            $this->assertStringContainsString(
                'We can&#039;t find any items matching these search criteria.',
                $responseBody
            );
        }
        $this->assertStringNotContainsString('Not visible simple product', $responseBody);
    }

    /**
     * Data provider with array in params values
     *
     * @return array
     */
    public function searchParamsInArrayDataProvider(): array
    {
        return [
            'search_with_from_param_is_array' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => [],
                        'to' => 1,
                    ]
                ]
            ],
            'search_with_to_param_is_array' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 0,
                        'to' => [],
                    ]
                ]
            ],
            'search_with_params_in_array' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => ['0' => 1],
                        'to' => [1],
                    ]
                ]
            ],
            'search_with_params_in_array_in_array' => [
                [
                    'name' => '',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => ['0' => ['0' => 1]],
                        'to' => 1,
                    ]
                ]
            ],
            'search_with_name_param_is_array' => [
                [
                    'name' => [],
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 0,
                        'to' => 20,
                    ]
                ]
            ]
        ];
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

    /**
     * Data provider with strings for quick search.
     *
     * @return array
     */
    public function testDataForAttributesCombination(): array
    {
        return [
            'search_product_by_name_and_price' => [
                [
                    'name' => 'Simple product name',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 99,
                        'to' => 101,
                    ],
                    'test_searchable_attribute' => '',
                ],
                true
            ],
            'search_product_by_name_and_price_not_shown' => [
                [
                    'name' => 'Simple product name',
                    'sku' => '',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 101,
                        'to' => 102,
                    ],
                    'test_searchable_attribute' => '',
                ],
                false
            ],
            'search_product_by_sku' => [
                [
                    'name' => '',
                    'sku' => 'simple_for_search',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 99,
                        'to' => 101,
                    ],
                    'test_searchable_attribute' => '',
                ],
                true
            ],
            'search_product_by_sku_not_shown' => [
                [
                    'name' => '',
                    'sku' => 'simple_for_search',
                    'description' => '',
                    'short_description' => '',
                    'price' => [
                        'from' => 990,
                        'to' => 1010,
                    ],
                    'test_searchable_attribute' => '',
                ],
                false
            ],
        ];
    }
}
