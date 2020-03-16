<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SendFriend;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test SendFriend configuration resolves correctly in StoreConfig
 */
class StoreConfigTest extends GraphQlAbstract
{
    public function testSendFriendFieldsAreReturnedWithoutError()
    {
        $query = $this->getStoreConfigQuery();

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('send_friend', $response['storeConfig']);
        $this->assertArrayHasKey('enabled_for_customers', $response['storeConfig']['send_friend']);
        $this->assertArrayHasKey('enabled_for_guests', $response['storeConfig']['send_friend']);
        $this->assertNotNull($response['storeConfig']['send_friend']['enabled_for_customers']);
        $this->assertNotNull($response['storeConfig']['send_friend']['enabled_for_guests']);
    }

    /**
     * @magentoConfigFixture default_store sendfriend/email/enabled 0
     */
    public function testSendFriendDisabled()
    {
        $response = $this->graphQlQuery($this->getStoreConfigQuery());

        $this->assertResponse(
            ['enabled_for_customers' => false, 'enabled_for_guests' => false],
            $response
        );
    }

    /**
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 0
     */
    public function testSendFriendEnabledGuestDisabled()
    {
        $response = $this->graphQlQuery($this->getStoreConfigQuery());

        $this->assertResponse(
            ['enabled_for_customers' => true, 'enabled_for_guests' => false],
            $response
        );
    }

    /**
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     */
    public function testSendFriendEnabledGuestEnabled()
    {
        $response = $this->graphQlQuery($this->getStoreConfigQuery());

        $this->assertResponse(
            ['enabled_for_customers' => true, 'enabled_for_guests' => true],
            $response
        );
    }

    /**
     * Assert response matches expected output
     *
     * @param array $expectedValues
     * @param array $response
     */
    private function assertResponse(array $expectedValues, array $response)
    {
        $this->assertArrayNotHasKey(
            'errors',
            $response
        );
        $this->assertArrayHasKey(
            'send_friend',
            $response['storeConfig']
        );
        $this->assertArrayHasKey(
            'enabled_for_customers',
            $response['storeConfig']['send_friend']
        );
        $this->assertArrayHasKey(
            'enabled_for_guests',
            $response['storeConfig']['send_friend']
        );
        $this->assertEquals(
            $expectedValues['enabled_for_customers'],
            $response['storeConfig']['send_friend']['enabled_for_customers']
        );
        $this->assertEquals(
            $expectedValues['enabled_for_guests'],
            $response['storeConfig']['send_friend']['enabled_for_guests']
        );
    }

    /**
     * Return simple storeConfig query to get sendFriend configuration
     *
     * @return string
     */
    private function getStoreConfigQuery()
    {
        return <<<QUERY
{
    storeConfig{
        id
        send_friend {
            enabled_for_customers
            enabled_for_guests
        }
    }
}
QUERY;
    }
}
