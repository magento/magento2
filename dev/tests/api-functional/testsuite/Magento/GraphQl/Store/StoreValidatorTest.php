<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL `Store` header validation
 */
class StoreValidatorTest extends GraphQlAbstract
{
    /**
     * @param string $storeCode
     * @param string $errorMessage
     *
     * @dataProvider dataProviderInvalidStore
     * @magentoApiDataFixture Magento/Store/_files/inactive_store.php
     */
    public function testInvalidStoreHeader(string $storeCode, string $errorMessage)
    {
        $query
            = <<<QUERY
{
  storeConfig{
    code
  }
}
QUERY;
        $this->expectExceptionMessage($errorMessage);
        $this->graphQlMutation($query, [], '', ['Store' => $storeCode]);
    }

    /**
     * Data provider with invalid store codes and expected error messages
     *
     * @return array
     */
    public static function dataProviderInvalidStore(): array
    {
        return [
            'non_existing' => [
                'non_existing',
                'Requested store is not found'
            ],
            'inactive_store' => [
                'inactive_store',
                'Requested store is not found'
            ]
        ];
    }
}
