<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class for Store Config customers settings
 */
class StoreConfigTest extends GraphQlAbstract
{
    /**
     * @magentoConfigFixture default_store customer/password/autocomplete_on_storefront 1
     * @magentoConfigFixture default_store customer/password/minimum_password_length 6
     * @magentoConfigFixture default_store customer/password/required_character_classes_number 2
     *
     * @throws Exception
     */
    public function testGetCustomerStoreConfig()
    {
        $minimumPasswordLength = 6;
        $requiredCharacterClassesNumber = 2;

        $query = <<<QUERY
{
    storeConfig {
        autocomplete_on_storefront
        minimum_password_length
        required_character_classes_number
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('autocomplete_on_storefront', $response['storeConfig']);
        self::assertTrue($response['storeConfig']['autocomplete_on_storefront']);

        self::assertArrayHasKey('minimum_password_length', $response['storeConfig']);
        self::assertEquals($response['storeConfig']['minimum_password_length'], $minimumPasswordLength);

        self::assertArrayHasKey('required_character_classes_number', $response['storeConfig']);
        self::assertEquals(
            $response['storeConfig']['required_character_classes_number'],
            $requiredCharacterClassesNumber
        );
    }
}
