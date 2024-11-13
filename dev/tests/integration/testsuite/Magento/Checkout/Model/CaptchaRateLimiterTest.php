<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Captcha\Model\DefaultModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\ObjectManager;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Test CAPTCHA-based rate limiter.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 */
class CaptchaRateLimiterTest extends TestCase
{
    /**
     * @var CaptchaRateLimiter
     */
    private $model;

    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @var HttpRequest;
     */
    private $request;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $this->request = $objectManager->get(RequestInterface::class);
        $this->request->getServer()->set('REMOTE_ADDR', '127.0.0.1');
        $objectManager->removeSharedInstance(RemoteAddress::class);
        $this->captchaHelper = $objectManager->get(CaptchaHelper::class);
        $this->customerSession = $objectManager->get(CustomerSession::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->model = $objectManager->create(
            CaptchaRateLimiter::class,
            ['captchaId' => 'payment_processing_request']
        );
    }

    /**
     * Verify that limits work for logged-in customers.
     *
     * @return void
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms payment_processing_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 2
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 10
     */
    public function testLoggedInLimits(): void
    {
        //Logging in
        $customer = $this->customerRepo->get('customer@example.com');
        $this->customerSession->loginById($customer->getId());

        $this->model->limit();
        $this->model->limit();
        try {
            $this->model->limit();
            $limited = false;
        } catch (LocalizedException $exception) {
            $limited = true;
        }
        $this->assertTrue($limited);
    }

    /**
     * Verify that limits work for guest.
     *
     * @return void
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms payment_processing_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 2
     */
    public function testGuestLimits(): void
    {
        $this->model->limit();
        $this->model->limit();
        try {
            $this->model->limit();
            $limited = false;
        } catch (LocalizedException $exception) {
            $limited = true;
        }
        $this->assertTrue($limited);
    }

    /**
     * Verify that CAPTCHA is validated.
     *
     * @return void
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms payment_processing_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 2
     */
    public function testCaptchaValidation(): void
    {
        $this->model->limit();
        $this->model->limit();
        try {
            $this->model->limit();
            $limited = false;
        } catch (LocalizedException $exception) {
            $limited = true;
        }
        //CAPTCHA is required
        $this->assertTrue($limited);

        //Providing CAPTCHA value
        /** @var DefaultModel $captcha */
        $captcha = $this->captchaHelper->getCaptcha(CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM);
        $captcha->generate();
        $this->request->setPostValue(
            'captcha',
            [CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM => $captcha->getWord()]
        );
        $this->model->limit();
        //Providing CAPTCHA value in a header
        /** @var DefaultModel $captcha */
        $captcha = $this->captchaHelper->getCaptcha(CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM);
        $captcha->generate();
        $this->request->setPostValue(
            'captcha',
            [CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM => '']
        );
        $this->request->getHeaders()->addHeaderLine('X-Captcha', $captcha->getWord());
        $this->model->limit();

        //Providing invalid CAPTCHA value.
        $this->request->setPostValue(
            'captcha',
            [CaptchaPaymentProcessingRateLimiter::CAPTCHA_FORM => 'invalid']
        );
        $this->request->getHeaders()->removeHeader($this->request->getHeaders()->get('X-Captcha'));
        try {
            $this->model->limit();
            $limited = false;
        } catch (LocalizedException $exception) {
            $limited = true;
        }
        //CAPTCHA was validated
        $this->assertTrue($limited);
    }
}
