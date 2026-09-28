<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model;

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory as ResourceRuleFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Indexer\StateInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Covers with test catalog rule repository functionality.
 */
class CatalogRuleRepositoryTest extends TestCase
{
    /**
     * @var CatalogRuleRepositoryInterface
     */
    private $catalogRuleRepository;

    /**
     * @var RuleProductProcessor
     */
    private $ruleProductProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->catalogRuleRepository = Bootstrap::getObjectManager()->get(CatalogRuleRepositoryInterface::class);
        $this->ruleProductProcessor = Bootstrap::getObjectManager()->get(RuleProductProcessor::class);
    }

    /**
     * Verify that index is not invalidated after saving catalog rule.
     *
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_25_customer_group_all.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testIndexerShouldNotBeInvalidatedAfterSavingCatalogRule(): void
    {
        $ruleProductProcessor = Bootstrap::getObjectManager()->get(RuleProductProcessor::class);
        $rule = $this->getRuleByName('Test Catalog Rule With 25 Percent Off');

        // save active rule
        $this->markIndexerAsValid();
        $rule->setDescription('save active');
        $this->catalogRuleRepository->save($rule);
        self::assertEquals(StateInterface::STATUS_VALID, $ruleProductProcessor->getIndexer()->getStatus());

        // change status from active to inactive
        $this->markIndexerAsValid();
        $rule->setIsActive(0);
        $rule->setDescription('change status from active to inactive');
        $this->catalogRuleRepository->save($rule);
        self::assertEquals(StateInterface::STATUS_VALID, $ruleProductProcessor->getIndexer()->getStatus());

        // save inactive rule
        $this->markIndexerAsValid();
        $rule->setDescription('save inactive');
        $this->catalogRuleRepository->save($rule);
        self::assertEquals(StateInterface::STATUS_VALID, $ruleProductProcessor->getIndexer()->getStatus());

        // change status from inactive to active
        $this->markIndexerAsValid();
        $rule->setIsActive(1);
        $rule->setDescription('change status from inactive to active');
        $this->catalogRuleRepository->save($rule);
        self::assertEquals(StateInterface::STATUS_VALID, $ruleProductProcessor->getIndexer()->getStatus());
    }

    /**
     * Verify that index is not invalidated after deleting catalog rule.
     *
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_25_customer_group_all.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testIndexerShouldNotBeInvalidatedAfterDeletingCatalogRule(): void
    {
        $ruleProductProcessor = Bootstrap::getObjectManager()->get(RuleProductProcessor::class);
        $rule = $this->getRuleByName('Test Catalog Rule With 25 Percent Off');
        $this->catalogRuleRepository->delete($rule);
        self::assertEquals(StateInterface::STATUS_VALID, $ruleProductProcessor->getIndexer()->getStatus());
    }

    /**
     * Retrieve catalog rule by name from db.
     *
     * @param string $name
     * @return RuleInterface
     */
    private function getRuleByName(string $name): RuleInterface
    {
        $catalogRuleResource = Bootstrap::getObjectManager()->get(ResourceRuleFactory::class)->create();
        $select = $catalogRuleResource->getConnection()->select();
        $select->from($catalogRuleResource->getMainTable(), RuleInterface::RULE_ID);
        $select->where(RuleInterface::NAME . ' = ?', $name);
        $ruleId = $catalogRuleResource->getConnection()->fetchOne($select);

        return $this->catalogRuleRepository->get((int)$ruleId);
    }

    /**
     * @return void
     */
    private function markIndexerAsValid(): void
    {
        $state = $this->ruleProductProcessor->getIndexer()->getState();
        $state->setStatus(StateInterface::STATUS_VALID);
        $this->ruleProductProcessor->getIndexer()->setState($state);
    }
}
