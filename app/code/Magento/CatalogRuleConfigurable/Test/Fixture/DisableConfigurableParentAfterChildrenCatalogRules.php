<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Creates parent 50% and per-child 10% / 20% catalog price rules (requires product fixture).
 *
 * Pass {@see self::DISABLE_CONFIGURABLE_PARENT} => true to disable SKU configurable after rules
 * (e.g. child index tests). Omit or set false when the parent must stay enabled.
 *
 * Apply together with
 * {@see \Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductWithCustomOptionAndSimpleTierPrice}.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DisableConfigurableParentAfterChildrenCatalogRules implements RevertibleDataFixtureInterface
{
    public const DISABLE_CONFIGURABLE_PARENT = 'disable_configurable_parent';

    private const RESULT_DISABLED_CONFIGURABLE_PARENT = 'disabled_configurable_parent';

    private const CONFIGURABLE_RULE_NAME = 'Percent rule for configurable product';

    private const FIRST_SIMPLE_RULE_NAME = 'Percent rule for first simple product';

    private const SECOND_SIMPLE_RULE_NAME = 'Percent rule for second simple product';

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
     * @param array $data
     * @return DataObject
     */
    public function apply(array $data = []): DataObject
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_ADMINHTML);
        $websiteId = $this->getWebsiteIdForConfigurableSku();

        $this->savePercentRuleMatchingSku(self::CONFIGURABLE_RULE_NAME, $websiteId, 'configurable', 50);
        $this->savePercentRuleMatchingSku(self::FIRST_SIMPLE_RULE_NAME, $websiteId, 'simple_10', 10);
        $this->savePercentRuleMatchingSku(self::SECOND_SIMPLE_RULE_NAME, $websiteId, 'simple_20', 20);
        $this->indexBuilder->reindexFull();

        return new DataObject(
            [
                self::RESULT_DISABLED_CONFIGURABLE_PARENT => $this->disableConfigurableParentIfRequested($data),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        if ($data->getData(self::RESULT_DISABLED_CONFIGURABLE_PARENT)) {
            Bootstrap::getInstance()->loadArea(Area::AREA_ADMINHTML);
            $configurable = $this->productRepository->get('configurable');
            $configurable->setStatus(Status::STATUS_ENABLED);
            $this->productRepository->save($configurable);
        }

        $this->deleteRuleByName(self::CONFIGURABLE_RULE_NAME);
        $this->deleteRuleByName(self::FIRST_SIMPLE_RULE_NAME);
        $this->deleteRuleByName(self::SECOND_SIMPLE_RULE_NAME);
        $this->indexBuilder->reindexFull();
    }

    /**
     * Persists a percent catalog rule whose condition is a single SKU equality match.
     *
     * @param string $ruleName
     * @param int $websiteId
     * @param string $sku
     * @param int $discountPercent
     * @return void
     */
    private function savePercentRuleMatchingSku(
        string $ruleName,
        int $websiteId,
        string $sku,
        int $discountPercent
    ): void {
        $combineType = \Magento\CatalogRule\Model\Rule\Condition\Combine::class;
        $productConditionType = \Magento\CatalogRule\Model\Rule\Condition\Product::class;

        $rule = $this->ruleFactory->create();
        $rule->loadPost(
            [
                'name' => $ruleName,
                'is_active' => '1',
                'stop_rules_processing' => 0,
                'website_ids' => [$websiteId],
                'customer_group_ids' => self::CUSTOMER_GROUP_ID_NOT_LOGGED_IN,
                'discount_amount' => $discountPercent,
                'simple_action' => 'by_percent',
                'from_date' => '',
                'to_date' => '',
                'sort_order' => 0,
                'sub_is_enable' => 0,
                'sub_discount_amount' => 0,
                'conditions' => [
                    '1' => ['type' => $combineType, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
                    '1--1' => [
                        'type' => $productConditionType,
                        'attribute' => 'sku',
                        'operator' => '==',
                        'value' => $sku,
                    ],
                ],
            ]
        );
        $this->ruleRepository->save($rule);
    }

    /**
     * Website ID from the configurable product (requires prior product fixture; avoids Magento_Store).
     *
     * @return int
     */
    private function getWebsiteIdForConfigurableSku(): int
    {
        $websiteIds = $this->productRepository->get('configurable')->getWebsiteIds();
        $firstId = (int) (array_values($websiteIds)[0] ?? 0);
        if ($firstId === 0) {
            throw new \RuntimeException(
                'Configurable product must be assigned to a website; apply the product fixture first.'
            );
        }

        return $firstId;
    }

    /**
     * @param array $data
     * @return bool Whether the parent was disabled
     */
    private function disableConfigurableParentIfRequested(array $data): bool
    {
        if (empty($data[self::DISABLE_CONFIGURABLE_PARENT])) {
            return false;
        }
        $configurable = $this->productRepository->get('configurable');
        $configurable->setStatus(Status::STATUS_DISABLED);
        $this->productRepository->save($configurable);

        return true;
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
