<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Controller\Advanced;

use Magento\TestFramework\TestCase\AbstractController;
use Laminas\Stdlib\Parameters;

/**
 * Test cases for catalog advanced index using params.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexTest extends AbstractController
{
    /**
     * Advanced index test by params with the array in params.
     *
     * @magentoAppArea frontend
     * @dataProvider fromParamsInArrayDataProvider
     *
     * @param array $searchParams
     * @return void
     */
    public function testExecuteWithArrayInParams(array $searchParams): void
    {
        $this->getRequest()->setQuery(
            $this->_objectManager->create(
                Parameters::class,
                [
                    'values' => $searchParams
                ]
            )
        );
        $this->dispatch('catalogsearch/advanced/index');
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $this->getResponse()->getBody();
    }

    /**
     * Data provider with array in param values.
     *
     * @return array
     */
    public function fromParamsInArrayDataProvider(): array
    {
        return [
            'from_data_with_from_param_is_array' => [
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
            'from_data_with_to_param_is_array' => [
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
            'from_data_with_params_in_array' => [
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
            'from_data_with_params_in_array_in_array' => [
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
            'from_data_with_name_param_is_array' => [
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
}
