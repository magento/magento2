<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test email availability functionality
 */
class IsEmailAvailableTest extends GraphQlAbstract
{
    /**
     * @var ScopeConfigInterface|null
     */
    private ?ScopeConfigInterface $scopeConfig;

    /**
     * @var string|null
     */
    private $storeId;

    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        /* @var StoreResolverInterface $storeResolver */
        $storeResolver = $objectManager->get(StoreResolverInterface::class);
        $this->storeId = $storeResolver->getCurrentStoreId();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testEmailNotAvailable()
    {
        $query =
            <<<QUERY
{
  isEmailAvailable(email: "customer@example.com") {
    is_email_available
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('isEmailAvailable', $response);
        self::assertArrayHasKey('is_email_available', $response['isEmailAvailable']);
        $emailConfig = $this->scopeConfig->getValue(
            AccountManagement::GUEST_CHECKOUT_LOGIN_OPTION_SYS_CONFIG,
            ScopeInterface::SCOPE_STORE,
            $this->storeId
        );
        if (!$emailConfig) {
            self::assertTrue($response['isEmailAvailable']['is_email_available']);
        } else {
            self::assertFalse($response['isEmailAvailable']['is_email_available']);
        }
    }

    /**
     * Verify email availability
     */
    public function testEmailAvailable()
    {
        $query =
            <<<QUERY
{
  isEmailAvailable(email: "customer@example.com") {
    is_email_available
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('isEmailAvailable', $response);
        self::assertArrayHasKey('is_email_available', $response['isEmailAvailable']);
        self::assertTrue($response['isEmailAvailable']['is_email_available']);
    }

    /**
     */
    public function testEmailAvailableEmptyValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Email must be specified');

        $query =
            <<<QUERY
{
  isEmailAvailable(email: "") {
    is_email_available
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     */
    public function testEmailAvailableMissingValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field "isEmailAvailable" argument "email" of type "String!" is required');

        $query =
            <<<QUERY
{
  isEmailAvailable {
    is_email_available
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     */
    public function testEmailAvailableInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Email is invalid');

        $query =
            <<<QUERY
{
  isEmailAvailable(email: "invalid-email") {
    is_email_available
  }
}
QUERY;
        $this->graphQlQuery($query);
    }
}
