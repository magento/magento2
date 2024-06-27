<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQlCache;

use Magento\Customer\Test\Fixture\Customer;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GraphQlTest extends GraphQlAbstract
{
    #[
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testMutation(): void
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $generateToken = <<<MUTATION
        mutation{
            generateCustomerToken
            (
                email:"{$customer->getEmail()}",
                password: "password"
            )
            {
                token
            }
        }
MUTATION;
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);
        $this->assertEquals('no-cache', $tokenResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $tokenResponse['headers']['Cache-Control']
        );
    }
}
