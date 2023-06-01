<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Block;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\ButtonLockManager;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class checks send friend email block
 *
 * @see \Magento\SendFriend\Block\Send
 *
 * @magentoAppArea frontend
 */
class SendTest extends TestCase
{
    /** @var array  */
    private $elementsXpath = [
        'sender name field' => "//input[@name='sender[name]']",
        'sender email field' => "//input[@name='sender[email]']",
        'sender message field' => "//textarea[@name='sender[message]']",
        'recipient name field' => "//input[contains(@name, 'recipients[name]')]",
        'recipient email field' => "//input[contains(@name, 'recipients[email]')]",
        'submit button' => "//button[@type='submit']/span[contains(text(), 'Send Email')]",
        'notice massage' =>  "//div[@id='max-recipient-message']"
            . "/span[contains(text(), 'Maximum 1 email addresses allowed.')]"
    ];

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Send */
    private $block;

    /** @var Session */
    private $session;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Send::class)
            ->setButtonLockManager(Bootstrap::getObjectManager()->create(ButtonLockManager::class));
        $this->session = $this->objectManager->get(Session::class);
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->session->logout();
    }

    /**
     * @dataProvider formDataProvider
     *
     * @param string $field
     * @param string $value
     * @return void
     */
    public function testGetCustomerFieldFromFormData(string $field, string $value): void
    {
        $formData = ['sender' => [$field => $value]];
        $this->block->setFormData($formData);
        $this->assertEquals(trim($value), $this->_callBlockMethod($field));
    }

    /**
     * @return array
     */
    public function formDataProvider(): array
    {
        return [
            ['name', 'Customer Form Name'],
            ['email', 'customer_form_email@example.com']
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @dataProvider customerSessionDataProvider
     *
     * @magentoAppIsolation enabled
     *
     * @param string $field
     * @param string $value
     * @return void
     */
    public function testGetCustomerFieldFromSession(string $field, string $value): void
    {
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');
        $this->session->setCustomerDataAsLoggedIn($customer);
        $this->assertEquals($value, $this->_callBlockMethod($field));
    }

    /**
     * @return array
     */
    public function customerSessionDataProvider(): array
    {
        return [
            ['name', 'John Smith'],
            ['email', 'customer@example.com']
        ];
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/max_recipients 1
     *
     * @return void
     */
    public function testBlockAppearance(): void
    {
        $this->block->setTemplate('Magento_SendFriend::send.phtml');
        $html = preg_replace('#<script(.*?)>#i', '', $this->block->toHtml());
        foreach ($this->elementsXpath as $key => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath($xpath, $html),
                sprintf('The %s field is not found on the page', $key)
            );
        }
    }

    /**
     * Call block method based on form field
     *
     * @param string $field
     * @return null|string
     */
    protected function _callBlockMethod(string $field): ?string
    {
        switch ($field) {
            case 'name':
                return $this->block->getUserName();
            case 'email':
                return $this->block->getEmail();
            default:
                return null;
        }
    }
}
