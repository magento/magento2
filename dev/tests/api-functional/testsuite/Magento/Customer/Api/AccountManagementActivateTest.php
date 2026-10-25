<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\TestFramework\Helper\Bootstrap;

class AccountManagementActivateTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/customers/activate';

    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
    }

    #[
        ConfigFixture("customer/create_account/confirm", 1),
        DataFixture(
            CustomerFixture::class,
            ['confirmation' => 'CONFIRM-INIT'],
            'customer'
        )
    ]
    public function testActivateCustomerAnonymous(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $om = Bootstrap::getObjectManager();
        $customerRepository = $om->get(CustomerRepositoryInterface::class);
        $customerEntity = $customerRepository->getById((int)$customer->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH
                    . '?email=' . rawurlencode($customer->getEmail())
                    . '&confirmationKey=' . $customerEntity->getConfirmation(),
                'httpMethod' => RestRequest::HTTP_METHOD_POST,
            ],
        ];

        $requestData = [
            'email' => $customer->getEmail(),
            'confirmationKey' => $customerEntity->getConfirmation(),
            'confirmation_key' => $customerEntity->getConfirmation(),
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals((int)$customer->getId(), (int)$result['id']);
    }

    /**
     * Require confirmation for new accounts.
     *
     * @magentoConfigFixture default_store customer/create_account/confirm 1
     */
    #[
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'anon.activate.invalidkey@example.com',
                'confirmation' => 'CONFIRM-ABCDE'
            ],
            'customer_invalid_key'
        )
    ]
    public function testActivateCustomerAnonymousWithInvalidKey(): void
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer_invalid_key');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestRequest::HTTP_METHOD_POST,
            ],
        ];

        $requestData = [
            'email' => $customer->getEmail(),
            'confirmationKey' => 'WRONG-KEY',
        ];

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail('Expected exception for invalid confirmation key did not occur.');
        } catch (\Exception $e) {
            // API should reject with 400 for invalid key
            $this->assertEquals(400, $e->getCode(), 'Expected HTTP 400 on invalid confirmation key');
        }
    }

    /**
     * Require confirmation for new accounts.
     *
     * @magentoConfigFixture default_store customer/create_account/confirm 1
     */
    public function testActivateCustomerAnonymousMissingFields(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestRequest::HTTP_METHOD_POST,
            ],
        ];

        // Missing both email and confirmationKey
        try {
            $this->_webApiCall($serviceInfo, []);
            $this->fail('Expected exception for missing required fields did not occur.');
        } catch (\Exception $e) {
            $this->assertEquals(400, $e->getCode(), 'Expected HTTP 400 on missing required fields');
        }
    }
}
