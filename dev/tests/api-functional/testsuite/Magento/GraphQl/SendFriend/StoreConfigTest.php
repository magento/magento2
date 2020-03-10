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
        $query = $this->getQuery();

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('sendFriend', $response['storeConfig']);
        $this->assertArrayHasKey('enabled', $response['storeConfig']['sendFriend']);
        $this->assertArrayHasKey('allow_guest', $response['storeConfig']['sendFriend']);
        $this->assertNotNull($response['storeConfig']['sendFriend']['enabled']);
        $this->assertNotNull($response['storeConfig']['sendFriend']['allow_guest']);
    }

    /**
     * @magentoConfigFixture default_store sendfriend/email/enabled 0
     */
    public function testSendFriendDisabled()
    {
        $response = $this->graphQlQuery($this->getQuery());

        $this->assertResponse(
            ['enabled' => false, 'allow_guest' => false],
            $response
        );
    }

    /**
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 0
     */
    public function testSendFriendEnabledGuestDisabled()
    {
        $response = $this->graphQlQuery($this->getQuery());

        $this->assertResponse(
            ['enabled' => true, 'allow_guest' => false],
            $response
        );
    }

    /**
     * @magentoConfigFixture default_store sendfriend/email/enabled 1
     * @magentoConfigFixture default_store sendfriend/email/allow_guest 1
     */
    public function testSendFriendEnabledGuestEnabled()
    {
        $response = $this->graphQlQuery($this->getQuery());

        $this->assertResponse(
            ['enabled' => true, 'allow_guest' => true],
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
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertArrayHasKey('sendFriend', $response['storeConfig']);
        $this->assertArrayHasKey('enabled', $response['storeConfig']['sendFriend']);
        $this->assertArrayHasKey('allow_guest', $response['storeConfig']['sendFriend']);
        $this->assertEquals($expectedValues['enabled'], $response['storeConfig']['sendFriend']['enabled']);
        $this->assertEquals($expectedValues['allow_guest'], $response['storeConfig']['sendFriend']['allow_guest']);
    }

    /**
     * Return simple storeConfig query to get sendFriend configuration
     *
     * @return string
     */
    private function getQuery()
    {
        return <<<QUERY
{
    storeConfig{
        id
        sendFriend {
            enabled
            allow_guest
        }
    }
}
QUERY;
    }
}
