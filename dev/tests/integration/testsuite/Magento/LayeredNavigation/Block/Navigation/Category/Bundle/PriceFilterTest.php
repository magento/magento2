<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category\Bundle;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Module\Manager;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;

/**
 * Provides price filter tests for bundle products in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class PriceFilterTest extends AbstractFiltersTest
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleManager = $this->objectManager->get(Manager::class);
        //This check is needed because LayeredNavigation independent of Magento_Bundle
        if (!$this->moduleManager->isEnabled('Magento_Bundle')) {
            $this->markTestSkipped('Magento_Bundle module disabled.');
        }
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_and_fixed_bundle_products_in_category.php
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step 10
     * @return void
     */
    public function testGetFilters(): void
    {
        $this->getCategoryFiltersAndAssert(
            ['bundle-product' => 20.00],
            ['is_filterable' => '1'],
            [
                ['label' => '$10.00 - $19.99', 'value' => '10-20', 'count' => 1],
                ['label' => '$20.00 and above', 'value' => '20-30', 'count' => 1],
            ],
            'Category 1'
        );
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_CATEGORY;
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'price';
    }

    /**
     * @inheritdoc
     */
    protected function prepareFilterItems(AbstractFilter $filter): array
    {
        $items = [];
        /** @var Item $item */
        foreach ($filter->getItems() as $item) {
            $item = [
                'label' =>  strip_tags(__($item->getData('label'))->render()),
                'value' => $item->getData('value'),
                'count' => $item->getData('count'),
            ];
            $items[] = $item;
        }

        return $items;
    }
}
