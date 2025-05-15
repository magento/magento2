<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SalesRule;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAllCartRulesTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test to retrieve all cart rules when promo/graphql/share_all_sales_rule is enabled.
     *
     * @throws Exception
     */
    #[
        Config('promo/graphql/share_all_sales_rule', 1),
        DataFixture(SalesRuleFixture::class, as: 'rule1'),
        DataFixture(SalesRuleFixture::class, as: 'rule2'),
        DataFixture(SalesRuleFixture::class, as: 'rule3'),
        DataFixture(SalesRuleFixture::class, ['is_active' => 0], as: 'rule4')
    ]
    public function testGetAllCartRules(): void
    {
        $this->assertEmpty(
            array_diff(
                array_column($this->graphQlQuery($this->getAllSalesRulesQuery()), 'name'),
                array_column($this->fetchAllSalesRules(), 'name')
            )
        );
    }

    /**
     *  Test to retrieve all sales rules when promo/graphql/share_all_sales_rule is disabled.
     *
     * @throws Exception
     */
    #[
        Config('promo/graphql/share_all_sales_rule', 0)
    ]
    public function testGetAllCartRulesWhenConfigDisabled(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            "Sharing Cart Rules information is disabled or not configured."
        );
        $this->graphQlQuery($this->getAllSalesRulesQuery());
    }

    /**
     * Get all sales rules
     *
     * @return array[]
     */
    private function fetchAllSalesRules(): array
    {
        return [
            "allCartRules" => [
                ['name' => $this->fixtures->get('rule1')->getName()],
                ['name' => $this->fixtures->get('rule2')->getName()],
                ['name' => $this->fixtures->get('rule3')->getName()]
            ]
        ];
    }

    /**
     * Get all sales rules query
     *
     * @return string
     */
    private function getAllSalesRulesQuery(): string
    {
        return <<<QUERY
            {
              allCartRules {
                  name
              }
            }
        QUERY;
    }
}
