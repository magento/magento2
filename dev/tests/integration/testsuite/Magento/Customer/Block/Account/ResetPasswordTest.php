<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Account;

use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class checks password reset block output
 *
 * @see \Magento\Customer\Block\Account\Resetpassword
 * @magentoAppArea frontend
 */
class ResetPasswordTest extends TestCase
{
    private const FORM_XPATH = "//form[contains(@action, '?token=%s')]";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Resetpassword */
    private $block;

    /** @var Random */
    private $random;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->random = $this->objectManager->get(Random::class);
        $this->block = $this->layout->createBlock(Resetpassword::class);
        $this->block->setTemplate('Magento_Customer::form/resetforgottenpassword.phtml');
    }

    /**
     * @return void
     */
    public function testResetPasswordForm(): void
    {
        $token = $this->random->getUniqueHash();
        $this->block->setResetPasswordLinkToken($token);
        $output = $this->block->toHtml();
        $this->assertEquals(1, Xpath::getElementsCountForXpath(sprintf(self::FORM_XPATH, $token), $output));
        $this->assertContains('New Password', $output);
        $this->assertContains('Set a New Password', $output);
        $this->assertContains('Confirm New Password', $output);
    }
}
