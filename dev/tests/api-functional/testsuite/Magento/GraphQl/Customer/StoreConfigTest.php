<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\TestFramework\Fixture\Config;
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

    #[
        Config('customer/create_account/confirm', 1)
    ]
    public function testCreateAccountConfirmationEnabledStorefrontConfig()
    {
        $query = <<<QUERY
{
    storeConfig {
        create_account_confirmation
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('create_account_confirmation', $response['storeConfig']);
        self::assertTrue($response['storeConfig']['create_account_confirmation']);
    }

    #[
        Config('customer/create_account/confirm', 0)
    ]
    public function testCreateAccountConfirmationDisabledStorefrontConfig()
    {
        $query = <<<QUERY
{
    storeConfig {
        create_account_confirmation
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('create_account_confirmation', $response['storeConfig']);
        self::assertFalse($response['storeConfig']['create_account_confirmation']);
    }
}
