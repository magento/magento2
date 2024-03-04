<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\GraphQl\IntegrationGraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\Config;

/**
 * Class for Store Config Customer Access Token Lifetime settings
 */
class CustomerAccessTokenLifetimeResolverTest extends GraphQlAbstract
{
    #[
        Config('oauth/access_token_lifetime/customer', '2')
    ]
    public function testGetCustomerAccessTokenLifetimeAsString()
    {
        $this->assertEquals(
            $this->graphQlQuery($this->getQuery()),
            [
                'storeConfig' => [
                    "customer_access_token_lifetime" => 2
                ]
            ]
        );
    }

    #[
        Config('oauth/access_token_lifetime/customer', 2.5)
    ]
    public function testGetCustomerAccessTokenLifetimeAsFloat()
    {
        $this->assertEquals(
            $this->graphQlQuery($this->getQuery()),
            [
                'storeConfig' => [
                    "customer_access_token_lifetime" => 2.5
                ]
            ]
        );
    }

    #[
        Config('oauth/access_token_lifetime/customer', null)
    ]
    public function testGetCustomerAccessTokenLifetimeNull()
    {
        $this->assertEquals(
            $this->graphQlQuery($this->getQuery()),
            [
                'storeConfig' => [
                    "customer_access_token_lifetime" => null
                ]
            ]
        );
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
        {
            storeConfig {
                customer_access_token_lifetime
            }
        }
QUERY;
    }
}
