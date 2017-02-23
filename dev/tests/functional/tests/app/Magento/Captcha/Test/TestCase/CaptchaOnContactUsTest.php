<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnContactUsForm;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Captcha\Test\Page\ContactUs;

/**
 * Preconditions:
 * 1. Enable CAPTCHA for customer.
 *
 * Test Flow:
 * 1. Open contact us page.
 * 2. Send comment using captcha.
 *
 * @group Captcha
 * @ZephyrId MAGETWO-43609
 */
class CaptchaOnContactUsTest extends Injectable
{
    /**
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Assert Captcha.
     *
     * @var AssertCaptchaFieldOnContactUsForm
     */
    private $assertCaptcha;

    /**
     * ContactUs page.
     *
     * @var ContactUs
     */
    private $contactUs;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Injection data.
     *
     * @param TestStepFactory $stepFactory
     * @param AssertCaptchaFieldOnContactUsForm $assertCaptcha
     * @param ContactUs $contactUs
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnContactUsForm $assertCaptcha,
        ContactUs $contactUs
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->contactUs = $contactUs;
    }

    /**
     * Test creation for send comment using the contact us form with captcha.
     *
     * @param Customer $customer
     * @param null|string $configData
     * @return void
     */
    public function test(
        Customer $customer,
        $configData
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        $this->contactUs->open();
        $this->assertCaptcha->processAssertRegisterForm($this->contactUs);
        $this->contactUs->getFormWithCaptcha()->reloadCaptcha();
        $this->contactUs->getFormWithCaptcha()->sendComment($customer);
    }

    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
