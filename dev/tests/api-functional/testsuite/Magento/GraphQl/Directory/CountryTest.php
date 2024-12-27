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
class CountryTest extends GraphQlAbstract
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
    public function testGetDefaultStoreUSCountry()
    {
        $result = $this->graphQlQuery($this->getQuery('US'));
        $this->assertArrayHasKey('country', $result);
        $this->assertEquals('US', $result['country']['id']);
        $this->assertEquals('US', $result['country']['two_letter_abbreviation']);
        $this->assertEquals('USA', $result['country']['three_letter_abbreviation']);
        $this->assertEquals('United States', $result['country']['full_name_locale']);
        $this->assertEquals('United States', $result['country']['full_name_english']);
        $this->assertCount(65, $result['country']['available_regions']);
        $this->assertArrayHasKey('id', $result['country']['available_regions'][0]);
        $this->assertArrayHasKey('code', $result['country']['available_regions'][0]);
        $this->assertArrayHasKey('name', $result['country']['available_regions'][0]);
    }

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
    public function testGetTestStoreDECountry()
    {
        $result = $this->graphQlQuery(
            $this->getQuery('DE'),
            [],
            '',
            ['Store' => 'test']
        );
        $this->assertArrayHasKey('country', $result);
        $this->assertEquals('DE', $result['country']['id']);
        $this->assertEquals('DE', $result['country']['two_letter_abbreviation']);
        $this->assertEquals('DEU', $result['country']['three_letter_abbreviation']);
        $this->assertEquals('Germany', $result['country']['full_name_locale']);
        $this->assertEquals('Germany', $result['country']['full_name_english']);
        $this->assertCount(16, $result['country']['available_regions']);
        $this->assertArrayHasKey('id', $result['country']['available_regions'][0]);
        $this->assertArrayHasKey('code', $result['country']['available_regions'][0]);
        $this->assertArrayHasKey('name', $result['country']['available_regions'][0]);
    }

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
    public function testGetDefaultStoreDECountryNotFoundException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The country isn\'t available.');

        $this->graphQlQuery($this->getQuery('DE'));
    }

    public function testMissedInputParameterException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Country "id" value should be specified');

        $query = <<<QUERY
{
  country {
    available_regions {
      code
      id
      name
    }
  }
}
QUERY;

        $this->graphQlQuery($query);
    }

    /**
     * Get query
     *
     * @param string $countryId
     * @return string
     */
    private function getQuery(string $countryId): string
    {
        return <<<QUERY
query {
    country(id: {$countryId}) {
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
