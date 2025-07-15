<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogRule;

use Exception;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAllCatalogRulesTest extends GraphQlAbstract
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
     * Test to retrieve all catalog rules when catalog/rule/share_all_catalog_rules is enabled.
     *
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_all_catalog_rules', 1),
        DataFixture(CatalogRuleFixture::class, as: 'catalogrule1'),
        DataFixture(CatalogRuleFixture::class, as: 'catalogrule2'),
        DataFixture(CatalogRuleFixture::class, as: 'catalogrule3'),
        DataFixture(CatalogRuleFixture::class, ['is_active' => 0], as: 'catalogrule4')
    ]
    public function testGetAllCatalogRules(): void
    {
        $this->assertEmpty(
            array_diff(
                array_column($this->graphQlQuery($this->getAllCatalogRulesQuery()), 'name'),
                array_column($this->fetchAllCatalogRules(), 'name')
            )
        );
    }

    /**
     * Test to retrieve catalog rules when catalog/rule/share_all_catalog_rules is enabled.
     *
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_all_catalog_rules', 1)
    ]
    public function testGetAllCatalogRulesWithZeroResult(): void
    {
        $response = $this->graphQlQuery($this->getAllCatalogRulesQuery());
        $this->assertEmpty($response['allCatalogRules']);
    }

    /**
     *  Test to retrieve all catalog rules when catalog/rule/share_all_catalog_rules is disabled.
     *
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_all_catalog_rules', 0)
    ]
    public function testGetAllCatalogRulesWhenConfigDisabled(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            "Sharing catalog rules information is disabled or not configured."
        );
        $this->graphQlQuery($this->getAllCatalogRulesQuery());
    }

    /**
     * Get all catalog rules
     *
     * @return array[]
     */
    private function fetchAllCatalogRules(): array
    {
        return [
            'allCatalogRules' => [
                ['name' => $this->fixtures->get('catalogrule1')->getName()],
                ['name' => $this->fixtures->get('catalogrule2')->getName()],
                ['name' => $this->fixtures->get('catalogrule3')->getName()]
            ]
        ];
    }

    /**
     * Get all catalog rules query
     *
     * @return string
     */
    private function getAllCatalogRulesQuery(): string
    {
        return <<<QUERY
            {
              allCatalogRules {
                  name
              }
            }
        QUERY;
    }
}
