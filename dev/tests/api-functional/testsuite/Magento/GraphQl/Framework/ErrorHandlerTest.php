<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Framework;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests error handling for GraphQL.
 */
class ErrorHandlerTest extends GraphQlAbstract
{
    /**
     * Test that when not in developer mode, only the first error message is reported.
     *
     * This is important for performance optimization, since an infinite number of errors
     * can be reported for a single query.
     */
    public function testErrorHandlerReportsFirstErrorOnly()
    {
        $query = <<<QUERY
query {
  countries {
    full_name_english @aaaaaa @bbbbbb @cccccc
    full_name_locale @skip
    ...countryAbbrev
  }
}

fragment countryAbbrev on Country {
   two_letter_abbreviation @aaaaa
   three_letter_abbreviation @aaaaaa
}
QUERY;
        try {
            $this->graphQlQuery($query);
        } catch (\Exception $e) {
            $responseData = $e->getResponseData();
            self::assertCount(1, $responseData['errors']);

            $errorMsg = $responseData['errors'][0]['message'];
            self::assertMatchesRegularExpression('/Unknown directive \"@aaaaaa\"./', $errorMsg);
        }
    }
}
