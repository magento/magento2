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
class CountriesTest extends GraphQlAbstract
{
    public function testGetCountries()
    {
        $query = <<<QUERY
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

        $result = $this->graphQlQuery($query);
        $this->assertArrayHasKey('countries', $result);
        $this->assertArrayHasKey('id', $result['countries'][0]);
        $this->assertArrayHasKey('two_letter_abbreviation', $result['countries'][0]);
        $this->assertArrayHasKey('three_letter_abbreviation', $result['countries'][0]);
        $this->assertArrayHasKey('full_name_locale', $result['countries'][0]);
        $this->assertArrayHasKey('full_name_english', $result['countries'][0]);
        $this->assertArrayHasKey('available_regions', $result['countries'][0]);
    }

    public function testCheckCountriesForNullLocale()
    {
        $query = <<<QUERY
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

        $result = $this->graphQlQuery($query);
        $count = count($result['countries']);
        for ($i=0; $i < $count; $i++) {
            $this->assertNotNull($result['countries'][$i]['full_name_locale']);
        }
    }
}
