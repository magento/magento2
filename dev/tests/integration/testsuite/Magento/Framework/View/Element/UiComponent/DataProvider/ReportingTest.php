<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Represents FilterPool methods test class
 */
class ReportingTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/five_repository_customers.php
     * @magentoDbIsolation disabled
     * @dataProvider filtersDataProvider
     * @param array $filters
     * @param int $expectedCount
     */
    public function testSearchItemsByOrCondition(array $filters, int $expectedCount): void
    {
        $filterGroups = [];
        $filterGroups[] = $this->objectManager->create(FilterGroup::class)
            ->setFilters(
                [
                    $this->objectManager->create(Filter::class, ['data' => $filters[0]]),
                    $this->objectManager->create(Filter::class, ['data' => $filters[1]]),
                ]
            );
        $filterGroups[] = $this->objectManager->create(FilterGroup::class)
            ->setFilters([$this->objectManager->create(Filter::class, ['data' => $filters[2]])]);
        if (isset($filters[3], $filters[4])) {
            $filterGroups[] = $this->objectManager->create(FilterGroup::class)
                ->setFilters(
                    [
                        $this->objectManager->create(Filter::class, ['data' => $filters[3]]),
                        $this->objectManager->create(Filter::class, ['data' => $filters[4]]),
                    ]
                );
        }

        /** @var SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $this->objectManager->get(SearchCriteriaInterface::class);
        $searchCriteria->setFilterGroups($filterGroups);
        $searchCriteria->setRequestName('customer_listing_data_source');
        $searchCriteria->setSortOrders([]);

        /** @var Reporting $reporting */
        $reporting = $this->objectManager->get(Reporting::class);
        $collection = $reporting->search($searchCriteria);
        self::assertCount($expectedCount, $collection->getItems(), 'Wrong collection filters applied');
    }

    /**
     * @return array[]
     */
    public static function filtersDataProvider()
    {
        return [
            'variation 1 (filter OR filter) AND filter' => [
                'filters' => [
                    [
                        'field' => 'email',
                        'value' => '%1%',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'email',
                        'value' => '%2%',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'name',
                        'value' => 'John Smith',
                        'condition_type' => 'eq',
                    ],
                ],
                'expectedCount' => 2,
            ],
            'variation 2 (filter OR filter) AND filter' => [
                'filters' => [
                    [
                        'field' => 'email',
                        'value' => '%1%',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'name',
                        'value' => 'John Smith',
                        'condition_type' => 'eq',
                    ],
                    [
                        'field' => 'email',
                        'value' => '%example%',
                        'condition_type' => 'like',
                    ],
                ],
                'expectedCount' => 5,
            ],
            'variation 3 (filter OR filter) AND filter' => [
                'filters' => [
                    [
                        'field' => 'email',
                        'value' => 'customer%',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'name',
                        'value' => 'John%',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'email',
                        'value' => 'customer2@example.com',
                        'condition_type' => 'eq',
                    ],
                ],
                'expectedCount' => 1,
            ],
            'variation (filter OR filter) AND filter AND (filter OR filter)' => [
                'filters' => [
                    [
                        'field' => 'email',
                        'value' => 'customer%',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'name',
                        'value' => 'Test',
                        'condition_type' => 'eq',
                    ],
                    [
                        'field' => 'email',
                        'value' => 'customer%@example.com',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'name',
                        'value' => 'non existing',
                        'condition_type' => 'like',
                    ],
                    [
                        'field' => 'email',
                        'value' => 'customer3%',
                        'condition_type' => 'like',
                    ],
                ],
                'expectedCount' => 1,
            ],
        ];
    }
}
