<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Form;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;
use Magento\Customer\ViewModel\LoginButton;

/**
 * Class checks login form view
 *
 * @magentoAppArea frontend
 */
class LoginTest extends TestCase
{
    private const EMAIL_LABEL_XPATH = "//label[@for='email']/span[contains(text(), 'Email')]";
    private const PASSWORD_LABEL_XPATH = "//label[@for='pass' ]/span[contains(text(), 'Password')]";
    private const EMAIL_INPUT_XPATH = "//input[@name ='login[username]' and contains(@data-validate,'required:true')"
    . "and contains(@data-validate, \"'validate-email':true\")]";
    private const PASSWORD_INPUT_XPATH = "//input[@name='login[password]'"
    . "and contains(@data-validate,'required:true')]";
    private const SIGN_IN_BUTTON_XPATH = "//button[@type='submit']/span[contains(text(), 'Sign In')]";
    private const FORGOT_PASSWORD_LINK_PATH = "//a[contains(@href, 'customer/account/forgotpassword')]"
    . "/span[contains(text(), 'Forgot Your Password?')] ";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Login */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Login::class);
        $this->block->setTemplate('Magento_Customer::form/login.phtml');
        $this->block->setLoginButtonViewModel($this->objectManager->get(LoginButton::class));

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testLoginForm(): void
    {
        $result = $this->block->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::EMAIL_LABEL_XPATH, $result),
            'Email label does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::PASSWORD_LABEL_XPATH, $result),
            'Password label does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::EMAIL_INPUT_XPATH, $result),
            'Email input does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::PASSWORD_INPUT_XPATH, $result),
            'Password input does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::SIGN_IN_BUTTON_XPATH, $result),
            'Sign in button does not exist on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::FORGOT_PASSWORD_LINK_PATH, $result),
            'Forgot password link does not exist on the page'
        );
    }

    /**
     * @magentoConfigFixture current_store customer/password/autocomplete_on_storefront 1
     *
     * @return void
     */
    public function testAutocompletePasswordEnabled(): void
    {
        $this->assertFalse($this->block->isAutocompleteDisabled());
    }

    /**
     * @magentoConfigFixture current_store customer/password/autocomplete_on_storefront 0
     *
     * @return void
     */
    public function testAutocompletePasswordDisabled(): void
    {
        $this->assertTrue($this->block->isAutocompleteDisabled());
    }
}
