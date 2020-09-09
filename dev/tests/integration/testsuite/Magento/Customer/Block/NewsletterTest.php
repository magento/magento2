<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class check newsletter subscription block behavior
 *
 * @see \Magento\Customer\Block\Newsletter
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class NewsletterTest extends TestCase
{
    private const LABEL_XPATH = "//form[contains(@class, 'form-newsletter-manage')]"
    . "//span[contains(text(), 'Subscription option')]";
    private const CHECKBOX_XPATH = "//form[contains(@class, 'form-newsletter-manage')]"
    . "//input[@type='checkbox' and @name='is_subscribed']";
    private const CHECKBOX_TITLE_XPATH = "//form[contains(@class, 'form-newsletter-manage')]"
    . "//label/span[contains(text(), 'General Subscription')]";
    private const SAVE_BUTTON_XPATH = "//form[contains(@class, 'form-newsletter-manage')]"
    . "//button[@type='submit']/span[contains(text(), 'Save')]";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Newsletter */
    private $block;

    /** @var Session */
    private $customerSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Newsletter::class);
        $this->customerSession = $this->objectManager->get(Session::class);
    }

    /**
     * @return void
     */
    public function testSubscriptionCheckbox(): void
    {
        $this->customerSession->loginById(1);
        $html = $this->block->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::LABEL_XPATH, $html),
            'Subscription label is not present on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::CHECKBOX_XPATH, $html),
            'Subscription checkbox is not present on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::CHECKBOX_TITLE_XPATH, $html),
            'Subscription checkbox label is not present on the page'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::SAVE_BUTTON_XPATH, $html),
            'Subscription save button is not present on the page'
        );
    }
}
