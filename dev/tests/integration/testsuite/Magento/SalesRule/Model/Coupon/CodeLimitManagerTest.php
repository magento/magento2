<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Test for captcha based implementation.
 *
 * @magentoAppArea frontend
 */
class CodeLimitManagerTest extends TestCase
{
    /**
     * @var CodeLimitManager
     */
    private $manager;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $this->manager = $objectManager->get(CodeLimitManager::class);
        $this->customerSession = $objectManager->get(CustomerSession::class);
        /** @var Http $request */
        $request = $objectManager->get(RequestInterface::class);
        $request->getServer()->set('REMOTE_ADDR', '127.0.0.1');
        $objectManager->removeSharedInstance(RemoteAddress::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->logout();
        $this->customerSession->clearStorage();
    }

    /**
     * Log in customer by ID.
     *
     * @param int $id
     * @return void
     */
    private function loginCustomer(int $id): void
    {
        if (!$this->customerSession->loginById($id)) {
            throw new \RuntimeException('Failed to log in customer');
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     */
    public function testCounterDisabled()
    {
        $this->manager->checkRequest('fakeCode1');
        $this->loginCustomer(1);
        $this->manager->checkRequest('fakeCode2');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 3
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 5
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUnderLimit()
    {
        $this->manager->checkRequest('fakeCode3');
        $this->manager->checkRequest('fakeCode4');

        $this->loginCustomer(1);
        $this->manager->checkRequest('fakeCode5');
        $this->manager->checkRequest('fakeCode6');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 2
     *
     */
    public function testAboveLimitNotLoggedIn()
    {
        $this->expectException(\Magento\SalesRule\Api\Exception\CodeRequestLimitException::class);

        try {
            $this->manager->checkRequest('fakeCode7');
            $this->manager->checkRequest('fakeCode8');
        } catch (CodeRequestLimitException $exception) {
            $this->fail('Attempt denied before reaching the limit');
        }
        $this->manager->checkRequest('fakeCode9');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 2
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 10
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testAboveLimitLoggedIn()
    {
        $this->expectException(\Magento\SalesRule\Api\Exception\CodeRequestLimitException::class);

        try {
            $this->loginCustomer(1);
            $this->manager->checkRequest('fakeCode10');
            $this->manager->checkRequest('fakeCode11');
        } catch (CodeRequestLimitException $exception) {
            $this->fail('Attempt denied before reaching the limit');
        }
        $this->manager->checkRequest('fakeCode12');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 10
     * @magentoConfigFixture default_store customer/captcha/mode always
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testCustomerNotAllowedWithoutCode()
    {
        $this->expectException(\Magento\SalesRule\Api\Exception\CodeRequestLimitException::class);

        $this->loginCustomer(1);
        $this->manager->checkRequest('fakeCode13');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 10
     * @magentoConfigFixture default_store customer/captcha/mode always
     *
     */
    public function testGuestNotAllowedWithoutCode()
    {
        $this->expectException(\Magento\SalesRule\Api\Exception\CodeRequestLimitException::class);

        $this->manager->checkRequest('fakeCode14');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 2
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 10
     *
     * @magentoDataFixture Magento/SalesRule/_files/rules.php
     * @magentoDataFixture Magento/SalesRule/_files/coupons.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testLoggingOnlyInvalidCodes()
    {
        $this->expectException(\Magento\SalesRule\Api\Exception\CodeRequestLimitException::class);

        try {
            $this->loginCustomer(1);
            $this->manager->checkRequest('coupon_code');
            $this->manager->checkRequest('coupon_code');
            $this->manager->checkRequest('fakeCode15');
            $this->manager->checkRequest('fakeCode16');
        } catch (CodeRequestLimitException $exception) {
            $this->fail('Attempts are logged for existing codes');
        }
        $this->manager->checkRequest('fakeCode17');
    }
}
