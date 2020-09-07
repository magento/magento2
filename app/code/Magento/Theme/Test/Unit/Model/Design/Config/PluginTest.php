<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Model\Design\Config\Plugin;
use Magento\Theme\Model\DesignConfigRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /** @var ManagerInterface|MockObject */
    protected $eventManager;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var DesignConfigRepository|MockObject */
    protected $repository;

    /** @var DesignConfigInterface|MockObject */
    protected $designConfig;

    /** @var WebsiteInterface|MockObject */
    protected $website;

    /** @var StoreInterface|MockObject */
    protected $store;

    /** @var  Plugin */
    protected $plugin;

    protected function setUp(): void
    {
        $this->eventManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->repository = $this->createMock(DesignConfigRepository::class);
        $this->designConfig = $this->getMockForAbstractClass(
            DesignConfigInterface::class,
            [],
            '',
            false
        );
        $this->website = $this->getMockForAbstractClass(
            WebsiteInterface::class,
            [],
            '',
            false
        );
        $this->store = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            false
        );
        $this->plugin = new Plugin($this->eventManager, $this->storeManager);
    }

    public function testAfterSave()
    {
        $this->designConfig->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn('website');
        $this->designConfig->expects($this->once())
            ->method('getScopeId')
            ->willReturn(1);
        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->with(1)
            ->willReturn($this->website);

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'admin_system_config_changed_section_design',
                ['website' => $this->website, 'store' => '']
            );
        $this->plugin->afterSave($this->repository, $this->designConfig);
    }

    public function testAfterSaveDispatchWithStore()
    {
        $this->designConfig->expects($this->exactly(2))
            ->method('getScope')
            ->willReturn('store');
        $this->designConfig->expects($this->once())
            ->method('getScopeId')
            ->willReturn(1);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with(1)
            ->willReturn($this->store);

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'admin_system_config_changed_section_design',
                ['website' => '', 'store' => $this->store]
            );
        $this->plugin->afterSave($this->repository, $this->designConfig);
    }
}
