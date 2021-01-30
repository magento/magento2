<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

use Laminas\Stdlib\Parameters;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement as CustomerAccountManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Security\Model\ConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

/**
 * Tests for account manager plugin.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AccountManagementTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Manager */
    private $moduleManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var ConfigInterface */
    private $securityConfig;

    /** @var RequestInterface */
    private $request;

    /** @var Phrase */
    private $errorMessage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $this->objectManager->get(Manager::class);
        //This check is needed because Magento_Security independent of Magento_Customer
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            $this->markTestSkipped('Magento_Customer module disabled.');
        }
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->request = $this->objectManager->get(RequestInterface::class);
        $this->securityConfig = $this->objectManager->get(ConfigInterface::class);
        $this->errorMessage = __(
            'We received too many requests for password resets. Please wait and try again later or contact %1.',
            $this->securityConfig->getCustomerServiceEmail()
        );
    }

    /**
     * @return void
     */
    public function testPluginIsRegistered(): void
    {
        $pluginInfo = $this->objectManager->get(PluginList::class)->get(CustomerAccountManagement::class);
        $this->assertSame(
            AccountManagement::class,
            $pluginInfo['security_check_customer_password_reset_attempt']['instance']
        );
    }

    /**
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoDataFixture Magento/Security/_files/customer_reset_password.php
     *
     * @return void
     */
    public function testMaxNumberPasswordResetRequests(): void
    {
        $this->prepareServerParameters();
        $this->expectExceptionObject(new SecurityViolationException($this->errorMessage));
        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            CustomerAccountManagement::EMAIL_REMINDER
        );
    }

    /**
     * @magentoConfigFixture current_store customer/password/min_time_between_password_reset_requests 10
     * @magentoDataFixture Magento/Security/_files/customer_reset_password.php
     *
     * @return void
     */
    public function testTimeBetweenPasswordResetRequests(): void
    {
        $this->prepareServerParameters();
        $this->expectExceptionObject(new SecurityViolationException($this->errorMessage));
        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            CustomerAccountManagement::EMAIL_REMINDER
        );
    }

    /**
     * @magentoConfigFixture current_store customer/password/password_reset_protection_type 0
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoDataFixture Magento/Security/_files/customer_reset_password.php
     *
     * @return void
     */
    public function testPasswordResetProtectionTypeDisabled(): void
    {
        $this->prepareServerParameters();
        $result = $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            CustomerAccountManagement::EMAIL_REMINDER
        );
        $this->assertTrue($result);
    }

    /**
     * @magentoConfigFixture current_store customer/password/password_reset_protection_type 1
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoDataFixture Magento/Security/_files/customer_reset_password.php
     *
     * @return void
     */
    public function testPasswordResetProtectionTypeByIpAndEmail(): void
    {
        $this->prepareServerParameters();
        $this->expectExceptionObject(new SecurityViolationException($this->errorMessage));
        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            CustomerAccountManagement::EMAIL_REMINDER
        );
    }

    /**
     * @magentoConfigFixture current_store customer/password/password_reset_protection_type 2
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoDataFixture Magento/Security/_files/customer_reset_password.php
     *
     * @return void
     */
    public function testPasswordResetProtectionTypeByIp(): void
    {
        $this->markTestSkipped('Test blocked by issue MC-32988.');
        $this->prepareServerParameters();
        $this->expectExceptionObject(new SecurityViolationException($this->errorMessage));
        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            CustomerAccountManagement::EMAIL_REMINDER
        );
    }

    /**
     * @magentoConfigFixture current_store customer/password/password_reset_protection_type 3
     * @magentoConfigFixture current_store customer/password/max_number_password_reset_requests 1
     * @magentoDataFixture Magento/Security/_files/customer_reset_password.php
     *
     * @return void
     */
    public function testPasswordResetProtectionTypeByEmail(): void
    {
        $this->prepareServerParameters();
        $this->expectExceptionObject(new SecurityViolationException($this->errorMessage));
        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            CustomerAccountManagement::EMAIL_REMINDER
        );
    }

    /**
     * Prepare server parameters.
     *
     * @return void
     */
    private function prepareServerParameters(): void
    {
        $parameters = $this->objectManager->create(Parameters::class);
        $parameters->set('REMOTE_ADDR', '127.0.0.1');
        $this->request->setServer($parameters);
    }
}
