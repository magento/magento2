<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directory;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's Coutries query
 */
class CountryTest extends GraphQlAbstract
{
    public function testGetCountry()
    {
        $query = <<<QUERY
query {
    country(id: "US") {
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

        $result = $this->graphQlQuery($query);
        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('id', $result['country']);
        $this->assertArrayHasKey('two_letter_abbreviation', $result['country']);
        $this->assertArrayHasKey('three_letter_abbreviation', $result['country']);
        $this->assertArrayHasKey('full_name_locale', $result['country']);
        $this->assertArrayHasKey('full_name_english', $result['country']);
        $this->assertArrayHasKey('available_regions', $result['country']);
        $this->assertArrayHasKey('id', $result['country']['available_regions'][0]);
        $this->assertArrayHasKey('code', $result['country']['available_regions'][0]);
        $this->assertArrayHasKey('name', $result['country']['available_regions'][0]);
    }

    /**
     */
    public function testGetCountryNotFoundException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The country isn\'t available.');

        $query = <<<QUERY
query {
    country(id: "BLAH") {
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

        $this->graphQlQuery($query);
    }

    /**
     */
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
}
