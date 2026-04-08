<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test the GraphQL `Store` header validation
 */
class StoreValidatorTest extends GraphQlAbstract
{
    /**
     * Test invalid store header
     *
     * @param string $storeCode
     * @param string $errorMessage
     * @magentoApiDataFixture Magento/Store/_files/inactive_store.php
     */
    #[DataProvider('dataProviderInvalidStore')]
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
