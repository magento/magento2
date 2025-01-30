<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\ResourceModel;

use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\SalesRule\Test\Fixture\ProductFoundInCartConditions as ProductFoundInCartConditionsFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Rule
     */
    private $resource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(
            DataFixtureStorageManager::class
        )->getStorage();
        $this->resource = Bootstrap::getObjectManager()->create(
            Rule::class
        );
    }

    /**
     * @magentoDataFixture Magento/SalesRule/_files/rule_custom_product_attribute.php
     */
    public function testAfterSave()
    {
        $items = $this->resource->getActiveAttributes();

        $this->assertEquals([['attribute_code' => 'attribute_for_sales_rule_1']], $items);
    }

    #[
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'category_ids', 'value' => '2'],
            'cond11'
        ),
        DataFixture(
            ProductFoundInCartConditionsFixture::class,
            ['conditions' => ['$cond11$']],
            'cond1'
        ),
        DataFixture(
            RuleFixture::class,
            ['discount_amount' => 50, 'conditions' => ['$cond1$'], 'is_active' => 0],
            'rule1'
        )
    ]
    public function testGetActiveAttributes()
    {
        $rule = $this->fixtures->get('rule1');
        $rule->setIsActive(1);
        $rule->save();
        $items = $this->resource->getActiveAttributes();
        $this->assertEquals([['attribute_code' => 'category_ids']], $items);
    }
}
