<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit\Framework\Attributes\DataProvider;

#[
    AppArea('adminhtml')
]
class IndexTest extends AbstractBackendController
{
    private const RULE_NAMES = [
        'priority_grid_rule_ten',
        'priority_grid_rule_zero',
        'priority_grid_rule_two',
    ];

    #[
        DataProvider('sortDirectionDataProvider'),
        DataFixture(CatalogRuleFixture::class, ['name' => 'priority_grid_rule_ten', 'sort_order' => 10]),
        DataFixture(CatalogRuleFixture::class, ['name' => 'priority_grid_rule_zero', 'sort_order' => 0]),
        DataFixture(CatalogRuleFixture::class, ['name' => 'priority_grid_rule_two', 'sort_order' => 2]),
    ]
    public function testGridSortsByPriorityNumerically(string $direction, array $expectedNames): void
    {
        $this->assertEquals($expectedNames, $this->getGridRuleNames(['name' => 'priority_grid_rule_'], $direction));
    }

    /**
     * @return array
     */
    public static function sortDirectionDataProvider(): array
    {
        return [
            'ascending' => ['asc', ['priority_grid_rule_zero', 'priority_grid_rule_two', 'priority_grid_rule_ten']],
            'descending' => ['desc', ['priority_grid_rule_ten', 'priority_grid_rule_two', 'priority_grid_rule_zero']],
        ];
    }

    #[
        DataFixture(CatalogRuleFixture::class, ['name' => 'priority_grid_rule_ten', 'sort_order' => 10]),
        DataFixture(CatalogRuleFixture::class, ['name' => 'priority_grid_rule_zero', 'sort_order' => 0]),
        DataFixture(CatalogRuleFixture::class, ['name' => 'priority_grid_rule_two', 'sort_order' => 2]),
    ]
    public function testGridFiltersByPriorityRange(): void
    {
        $this->assertEquals(
            ['priority_grid_rule_two', 'priority_grid_rule_ten'],
            $this->getGridRuleNames(
                ['name' => 'priority_grid_rule_', 'sort_order' => ['from' => '1', 'to' => '10']],
                'asc'
            )
        );
    }

    /**
     * Dispatches the catalog price rule grid and returns the fixture rule names in the rendered row order.
     *
     * @param array $filter
     * @param string $direction
     * @return string[]
     */
    private function getGridRuleNames(array $filter, string $direction): array
    {
        $this->getRequest()->setParams(
            [
                'filter' => base64_encode(http_build_query($filter)),
                'sort' => 'sort_order',
                'dir' => $direction,
            ]
        );
        $this->dispatch('backend/catalog_rule/promo_catalog/index');
        $html = $this->getResponse()->getBody();

        $this->assertStringContainsString('data-column="sort_order"', $html);

        $positions = [];
        foreach (self::RULE_NAMES as $name) {
            if (preg_match('/>\s*' . $name . '\s*</', $html, $match, PREG_OFFSET_CAPTURE)) {
                $positions[$name] = $match[0][1];
            }
        }
        asort($positions);

        return array_keys($positions);
    }
}
