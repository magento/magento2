<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directory;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's Countries query
 */
class CountriesTest extends GraphQlAbstract
{
    /**
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/general/locale/code en_US
     * @magentoConfigFixture default/general/country/allow US
     * @magentoConfigFixture test_store general/locale/code en_US
     * @magentoConfigFixture test_store general/country/allow US,DE
     */
    public function testGetCountries()
    {
        $result = $this->graphQlQuery($this->getQuery());
        $this->assertArrayHasKey('countries', $result);
        $this->assertCount(1, $result['countries']);
        $this->assertArrayHasKey('id', $result['countries'][0]);
        $this->assertArrayHasKey('two_letter_abbreviation', $result['countries'][0]);
        $this->assertArrayHasKey('three_letter_abbreviation', $result['countries'][0]);
        $this->assertArrayHasKey('full_name_locale', $result['countries'][0]);
        $this->assertArrayHasKey('full_name_english', $result['countries'][0]);
        $this->assertArrayHasKey('available_regions', $result['countries'][0]);

        $testStoreResult = $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            ['Store' => 'test']
        );
        $this->assertArrayHasKey('countries', $testStoreResult);
        $this->assertCount(2, $testStoreResult['countries']);
    }

    public function testCheckCountriesForNullLocale()
    {
        $result = $this->graphQlQuery($this->getQuery());
        $count = count($result['countries']);
        for ($i=0; $i < $count; $i++) {
            $this->assertNotNull($result['countries'][$i]['full_name_locale']);
        }
    }

    /**
     * Get query
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
query {
    countries {
        id
        two_letter_abbreviation
        three_letter_abbreviation
        full_name_locale
        full_name_english
        available_regions {
            id
            code
            name
        }
    }
}
QUERY;
    }
}
