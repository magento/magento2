<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Controller\Result;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class MessagePluginTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->messageManager = $this->objectManager->get(ManagerInterface::class);
        $this->messageManager->getMessages(true);
    }

    protected function tearDown(): void
    {
        $this->messageManager->getMessages(true);
    }

    public function testMessagesTooLargeForCookieStayInSession(): void
    {
        $text = 'The coupon code "' . str_repeat('a', PhpCookieManager::MAX_COOKIE_SIZE) . '" is not valid.';
        $this->messageManager->addErrorMessage($text);
        $plugin = $this->objectManager->create(
            MessagePlugin::class,
            ['cookieManager' => $this->objectManager->create(PhpCookieManager::class)]
        );
        $redirect = $this->objectManager->create(Redirect::class);

        $this->assertSame($redirect, $plugin->afterRenderResult($redirect, $redirect));

        $messages = $this->messageManager->getMessages(true)->getItems();
        $this->assertCount(1, $messages);
        $this->assertSame($text, $messages[0]->getText());
    }
}
