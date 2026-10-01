<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * 50% catalog price rule on configurable SKU (requires product data from a separate fixture).
 *
 * Apply together with
 * {@see \Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductWithCustomOptionAndSimpleTierPrice}.
 */
class ConfigurableProductWithPercentCatalogRule implements RevertibleDataFixtureInterface
{
    private const RULE_NAME = 'Percent rule for configurable product';

    /**
     * Customer group "NOT LOGGED IN" (avoids hard dependency on Magento_Customer in this module).
     */
    private const CUSTOMER_GROUP_ID_NOT_LOGGED_IN = 0;

    /**
     * @param CatalogRuleRepositoryInterface $ruleRepository
     * @param RuleFactory $ruleFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param IndexBuilder $indexBuilder
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private CatalogRuleRepositoryInterface $ruleRepository,
        private RuleFactory $ruleFactory,
        private RuleCollectionFactory $ruleCollectionFactory,
        private IndexBuilder $indexBuilder,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): DataObject
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_ADMINHTML);

        $websiteIds = $this->productRepository->get('configurable')->getWebsiteIds();
        $websiteId = (int) (array_values($websiteIds)[0] ?? 0);
        if ($websiteId === 0) {
            throw new \RuntimeException(
                'Configurable product must be assigned to a website; apply the product fixture first.'
            );
        }

        $rule = $this->ruleFactory->create();
        $rule->loadPost(
            [
                'name' => self::RULE_NAME,
                'is_active' => '1',
                'stop_rules_processing' => 0,
                'website_ids' => [$websiteId],
                'customer_group_ids' => self::CUSTOMER_GROUP_ID_NOT_LOGGED_IN,
                'discount_amount' => 50,
                'simple_action' => 'by_percent',
                'from_date' => '',
                'to_date' => '',
                'sort_order' => 0,
                'sub_is_enable' => 0,
                'sub_discount_amount' => 0,
                'conditions' => [
                    '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
                    '1--1' => [
                        'type' => Product::class,
                        'attribute' => 'sku',
                        'operator' => '==',
                        'value' => 'configurable',
                    ],
                ],
            ]
        );
        $this->ruleRepository->save($rule);
        $this->indexBuilder->reindexFull();

        return new DataObject([]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->deleteRuleByName(self::RULE_NAME);
        $this->indexBuilder->reindexFull();
    }

    /**
     * Deletes a catalog rule by exact name when present.
     *
     * @param string $name
     * @return void
     */
    private function deleteRuleByName(string $name): void
    {
        $collection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter('name', ['eq' => $name])
            ->setPageSize(1);
        /** @var Rule $rule */
        $rule = $collection->getFirstItem();
        if ($rule->getId()) {
            $this->ruleRepository->delete($rule);
        }
    }
}
