<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Plugin;

use Magento\Framework\View\Layout;
use Magento\Persistent\Block\Header\RememberMeInit;
use Magento\Persistent\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Persistent\Plugin\AddPersistentRememberMeInitPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddPersistentRememberMeInitPluginTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private $persistentData;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var AddPersistentRememberMeInitPlugin
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->persistentData = $this->createMock(Data::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->layout = $this->createMock(Layout::class);

        $this->plugin = new AddPersistentRememberMeInitPlugin(
            $this->persistentData,
            $this->customerSession
        );
    }

    public function testAroundGenerateElementsAddsBlock()
    {
        $this->customerSession->method('isLoggedIn')->willReturn(false);
        $this->persistentData->method('isEnabled')->willReturn(true);
        $this->persistentData->method('isRememberMeEnabled')->willReturn(true);

        $block = $this->createMock(RememberMeInit::class);
        $this->layout->method('getBlock')->willReturnMap([
            ['head.additional', $block],
            ['persistent_initial_configs', null]
        ]);

        $this->layout->expects($this->once())
            ->method('addBlock')
            ->with(RememberMeInit::class, 'persistent_initial_configs');

        $this->layout->expects($this->once())
            ->method('addOutputElement')
            ->with('persistent_initial_configs');

        $proceed = function () {
        };

        $this->plugin->aroundGenerateElements($this->layout, $proceed);
    }

    public function testAroundGenerateElementsDoesNotAddBlockWhenLoggedIn()
    {
        $this->customerSession->method('isLoggedIn')->willReturn(true);

        $this->layout->expects($this->never())
            ->method('addBlock');

        $this->layout->expects($this->never())
            ->method('addOutputElement');

        $proceed = function () {
        };

        $this->plugin->aroundGenerateElements($this->layout, $proceed);
    }
}
