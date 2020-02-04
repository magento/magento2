<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnContactUsForm;
use Magento\Contact\Test\Fixture\Comment;
use Magento\Captcha\Test\Page\ContactIndexCaptcha as ContactIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Enable captcha for customer.
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
     * Assert captcha on "Contact Us" page.
     *
     * @var AssertCaptchaFieldOnContactUsForm
     */
    private $assertCaptcha;

    /**
     * ContactIndex page.
     *
     * @var ContactIndex
     */
    private $contactIndex;

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
     * @param ContactIndex $contactIndex
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnContactUsForm $assertCaptcha,
        ContactIndex $contactIndex
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->contactIndex = $contactIndex;
    }

    /**
     * Test creation for send comment using the contact us form with captcha.
     *
     * @param Comment $comment
     * @param string $configData
     * @return void
     */
    public function test(
        Comment $comment,
        $configData
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        $this->contactIndex->open();
        $this->assertCaptcha->processAssertRegisterForm($this->contactIndex);
        $this->contactIndex->getContactUs()->fill($comment);
        $this->contactIndex->getContactUs()->reloadCaptcha();
        $this->contactIndex->getContactUs()->sendComment();
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
