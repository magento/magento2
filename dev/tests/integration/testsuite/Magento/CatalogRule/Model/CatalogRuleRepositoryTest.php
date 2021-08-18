<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model;

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory as ResourceRuleFactory;
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
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->catalogRuleRepository = Bootstrap::getObjectManager()->get(CatalogRuleRepositoryInterface::class);
    }

    /**
     * Verify index become invalid in case rule become inactive and stays active in case inactive rule has been saved.
     *
     * @magentoDataFixture Magento/CatalogRule/_files/catalog_rule_25_customer_group_all.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testIndexInvalidationAfterInactiveRuleSave(): void
    {
        $ruleProductProcessor = Bootstrap::getObjectManager()->get(RuleProductProcessor::class);
        $state = $ruleProductProcessor->getIndexer()->getState();
        $state->setStatus(StateInterface::STATUS_VALID);
        $ruleProductProcessor->getIndexer()->setState($state);
        $rule = $this->getRuleByName('Test Catalog Rule With 25 Percent Off');
        $rule->setIsActive(0);
        $this->catalogRuleRepository->save($rule);
        self::assertEquals(StateInterface::STATUS_INVALID, $ruleProductProcessor->getIndexer()->getStatus());
        $state = $ruleProductProcessor->getIndexer()->getState();
        $state->setStatus(StateInterface::STATUS_VALID);
        $ruleProductProcessor->getIndexer()->setState($state);
        $this->catalogRuleRepository->save($rule);
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
}
