<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for \Magento\PageCache\Model\App\Request\Http\IdentifierForSave
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IdentifierForSaveTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var IdentifierForSave
     */
    private $identifierForSave;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var string
     */
    private const COOKIE_VARY_STRING = 'X-Magento-Vary';

    /**
     * @var array
     */
    private $createdCustomerIds = [];

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->identifierForSave = $this->objectManager->get(IdentifierForSave::class);
        $this->context = $this->objectManager->get(Context::class);
        $this->cookieManager = $this->objectManager->get(CookieManagerInterface::class);
        $this->cookieMetadataFactory = $this->objectManager->get(CookieMetadataFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
    }

    protected function tearDown(): void
    {
        // Clean up customer session
        $customerSession = $this->objectManager->get(Session::class);
        if ($customerSession->isLoggedIn()) {
            $customerSession->logout();
        }

        // Clean up cookies
        $cookieMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()->setPath('/');
        $this->cookieManager->deleteCookie(self::COOKIE_VARY_STRING, $cookieMetadata);

        // Clean up created customers
        foreach ($this->createdCustomerIds as $customerId) {
            $this->deleteTestCustomer($customerId);
        }
        $this->createdCustomerIds = [];

        parent::tearDown();
    }

    /**
     * Create a test customer
     *
     * @param string $email
     * @return CustomerInterface
     */
    private function createTestCustomer(string $email): CustomerInterface
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId(1)
            ->setEmail($email)
            ->setFirstname('Test')
            ->setLastname('Customer')
            ->setGroupId(1)
            ->setStoreId(1);

        $passwordHash = $this->accountManagement->getPasswordHash('password123');

        $savedCustomer = $this->customerRepository->save($customer, $passwordHash);
        $this->createdCustomerIds[] = (int)$savedCustomer->getId();

        return $savedCustomer;
    }

    /**
     * Delete a test customer
     *
     * @param int $customerId
     * @return void
     */
    private function deleteTestCustomer(int $customerId): void
    {
        try {
            $this->customerRepository->deleteById($customerId);
        } catch (\Exception $e) {
            // Customer already deleted or doesn't exist
        }
    }

    /**
     * Test that cache identifier properly handles logged-in customers with cookie fallback
     *
     * This test validates the fix for the bug where "Create an Account" link was visible
     * on homepage after login due to empty context vary string on depersonalized homepage cache.
     * The fix ensures cookie vary string takes precedence over context vary string.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    #[
        ConfigFixture('system/full_page_cache/caching_application', '1', 'store'),
        ConfigFixture('system/full_page_cache/enabled', '1', 'store')
    ]
    public function testCookieVaryStringTakesPrecedenceOverContextVaryString()
    {
        // Create test customer and login
        $customer = $this->createTestCustomer('testcustomer1@example.com');
        $customerSession = $this->objectManager->get(Session::class);

        $customerSession->loginById($customer->getId());

        // Get cache identifiers with both context and cookie populated
        $result = $this->identifierForSave->getValue();

        // Verify that cache key is not empty for logged-in user
        $this->assertNotEmpty($result, 'Cache identifier for save should not be empty for logged-in user');

        // Get the current vary string from context
        $originalVaryString = $this->context->getVaryString();
        $this->assertNotEmpty($originalVaryString, 'Context vary string should not be empty for logged-in user');

        // Set the vary cookie to simulate a previous request
        $cookieMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()->setPath('/');

        $this->cookieManager->setSensitiveCookie(
            self::COOKIE_VARY_STRING,
            $originalVaryString,
            $cookieMetadata
        );

        // Clear the context vary string to simulate depersonalized homepage cache
        // This is the scenario that caused the bug: homepage cache was depersonalized
        // but customer cookie was still present
        $this->context->_resetState();

        // Verify context vary string is now empty (simulates depersonalized cache)
        $this->assertEmpty($this->context->getVaryString(), 'Context vary string should be empty after reset');

        // Get cache identifiers again - should still work due to cookie fallback
        // This is the CRITICAL assertion: cookie vary string takes precedence
        $resultWithEmptyContext = $this->identifierForSave->getValue();

        // Should still generate valid cache key due to cookie fallback
        $this->assertNotSame(
            '',
            $resultWithEmptyContext,
            'Cache identifier for save should work with empty context due to cookie fallback'
        );

        // Both cache keys should be identical - proving cookie vary string was used
        // This ensures logged-in customers see personalized content on homepage
        $this->assertSame(
            $result,
            $resultWithEmptyContext,
            'Cache identifier should be same with cookie fallback as with context vary string'
        );
    }

    /**
     * Test that cache identifier changes after customer login
     *
     * Validates that cache identifiers are different for guest vs logged-in customer,
     * ensuring personalized content is not served from guest cache.
     */
    #[
        ConfigFixture('system/full_page_cache/caching_application', '1', 'store'),
        ConfigFixture('system/full_page_cache/enabled', '1', 'store')
    ]
    public function testCacheIdentifierChangesAfterCustomerLogin()
    {
        // Get cache identifier for guest user (before login)
        $guestIdentifier = $this->identifierForSave->getValue();
        $this->assertNotEmpty($guestIdentifier, 'Guest cache identifier should not be empty');

        // Create test customer and login
        $customer = $this->createTestCustomer('testcustomer2@example.com');
        $customerSession = $this->objectManager->get(Session::class);

        $customerSession->loginById($customer->getId());

        // Get cache identifier after login
        $customerIdentifier = $this->identifierForSave->getValue();
        $this->assertNotEmpty($customerIdentifier, 'Customer cache identifier should not be empty');

        // Cache identifiers should be different for guest vs logged-in customer
        $this->assertNotEquals(
            $guestIdentifier,
            $customerIdentifier,
            'Cache identifier should change after customer login to ensure personalized content'
        );
    }

    /**
     * Test cache identifier with cookie vary string but no context vary string
     *
     * Validates that cookie vary string alone is sufficient to generate cache identifier.
     * This is the key behavior that fixes the homepage bug.
     */
    #[
        ConfigFixture('system/full_page_cache/caching_application', '1', 'store'),
        ConfigFixture('system/full_page_cache/enabled', '1', 'store')
    ]
    public function testCacheIdentifierWithOnlyCookieVaryString()
    {
        // Set a custom vary string in cookie (simulating logged-in user cookie)
        $testVaryString = 'customer-group-1';
        $cookieMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()->setPath('/');

        $this->cookieManager->setSensitiveCookie(
            self::COOKIE_VARY_STRING,
            $testVaryString,
            $cookieMetadata
        );

        // Ensure context vary string is empty (simulating depersonalized homepage)
        $this->context->_resetState();
        $this->assertEmpty($this->context->getVaryString(), 'Context vary string should be empty');

        // Get cache identifier - should use cookie vary string
        $result = $this->identifierForSave->getValue();

        // Should generate valid cache identifier from cookie alone
        $this->assertNotEmpty($result, 'Cache identifier should be generated from cookie vary string');

        // Verify the result is a valid cache identifier (hash)
        // The cache identifier is a hash, not the literal vary string
        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{40}$/',
            $result,
            'Cache identifier should be a valid SHA1 hash'
        );
    }
}
