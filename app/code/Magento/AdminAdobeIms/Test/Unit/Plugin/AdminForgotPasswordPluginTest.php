<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Plugin;

use Magento\AdminAdobeIms\Plugin\AdminForgotPasswordPlugin;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\User\Controller\Adminhtml\Auth\Forgotpassword;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminForgotPasswordPluginTest extends TestCase
{
    /**
     * @var AdminForgotPasswordPlugin
     */
    private $plugin;

    /**
     * @var RedirectFactory|MockObject
     */
    private $redirectFactory;

    /**
     * @var ImsConfig|MockObject
     */
    private $adminImsConfigMock;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->redirectFactory = $this->createMock(RedirectFactory::class);
        $this->adminImsConfigMock = $this->createMock(ImsConfig::class);
        $this->messageManagerMock = $this->createMock(MessageManagerInterface::class);

        $this->plugin = $objectManagerHelper->getObject(
            AdminForgotPasswordPlugin::class,
            [
                'redirectFactory' => $this->redirectFactory,
                'adminImsConfig' => $this->adminImsConfigMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    /**
     * Test plugin redirects to admin login when AdminAdobeIms Module is enabled
     *
     * @return void
     */
    public function testPluginRedirectsToLoginPageWhenModuleIsEnabled(): void
    {
        $subject = $this->createMock(Forgotpassword::class);
        $redirect = $this->createMock(Redirect::class);
        $redirect->method('setPath')
            ->willReturnSelf();

        $this->adminImsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $this->redirectFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($redirect);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('Please sign in with Adobe ID', null)
            ->willReturnSelf();

        $closure = function () {
            return $this->createMock(Redirect::class);
        };

        $this->assertEquals($redirect, $this->plugin->aroundExecute($subject, $closure));
    }

    /**
     * Test plugin proceeds when AdminAdobeIms Module is disabled
     *
     * @return void
     */
    public function testPluginProceedsWhenModuleIsDisabled(): void
    {
        $subject = $this->createMock(Forgotpassword::class);
        $redirect = $this->createMock(Redirect::class);

        $this->adminImsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(false);

        $this->redirectFactory
            ->expects($this->never())
            ->method('create')
            ->willReturn($redirect);

        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage')
            ->with('Please sign in with Adobe ID', null)
            ->willReturnSelf();

        $closure = function () {
            return $this->createMock(Redirect::class);
        };

        $this->assertEquals($redirect, $this->plugin->aroundExecute($subject, $closure));
    }
}
