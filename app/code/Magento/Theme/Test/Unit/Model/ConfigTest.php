<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme config model
 */
namespace Magento\Theme\Test\Unit\Model;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $themeMock;

    /**
     * @var MockObject
     */
    protected $configData;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $configCacheMock;

    /**
     * @var MockObject
     */
    protected $layoutCacheMock;

    /**
     * @var WriterInterface
     */
    protected $scopeConfigWriter;

    /**
     * @var Config
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /** @var $this->themeMock Theme */
        $this->themeMock = $this->createMock(Theme::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStores', 'isSingleStoreMode']
        );
        $this->configData = $this->getMockBuilder(Value::class)
            ->addMethods(['addFieldToFilter'])
            ->onlyMethods(['getCollection', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configCacheMock = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->layoutCacheMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $this->scopeConfigWriter = $this->createPartialMock(
            WriterInterface::class,
            ['save', 'delete']
        );

        $this->model = new Config(
            $this->configData,
            $this->scopeConfigWriter,
            $this->storeManagerMock,
            $this->getMockForAbstractClass(ManagerInterface::class),
            $this->configCacheMock,
            $this->layoutCacheMock
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->themeMock = null;
        $this->configData = null;
        $this->configCacheMock = null;
        $this->layoutCacheMock = null;
        $this->model = null;
    }

    /**
     * @return void
     * cover Config::assignToStore
     */
    public function testAssignToStoreInSingleStoreMode(): void
    {
        $this->storeManagerMock->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(true);

        $themePath = 'Magento/blank';
        /** Unassign themes from store */
        $configEntity = new DataObject(['value' => 6, 'scope_id' => 8]);

        $this->configData->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->configData);

        $this->configData
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1, $arg2) use ($configEntity) {
                if ($arg1 == 'scope' && $arg2 == ScopeInterface::SCOPE_STORES) {
                    return $this->configData;
                } elseif ($arg1 == 'path' && $arg2 == DesignInterface::XML_PATH_THEME_ID) {
                    return [$configEntity];
                }
            });

        $this->themeMock->expects($this->any())->method('getId')->willReturn(6);
        $this->themeMock->expects($this->any())->method('getThemePath')->willReturn($themePath);

        $this->scopeConfigWriter->expects($this->once())->method('delete');

        $this->scopeConfigWriter->expects($this->once())->method('save');

        $this->model->assignToStore($this->themeMock, [2, 3, 5]);
    }

    /**
     * @return void
     * cover Config::assignToStore
     */
    public function testAssignToStoreNonSingleStoreMode(): void
    {
        $this->storeManagerMock->expects($this->once())->method('isSingleStoreMode')->willReturn(false);

        $themePath = 'Magento/blank';
        /** Unassign themes from store */
        $configEntity = new DataObject(['value' => 6, 'scope_id' => 8]);

        $this->configData->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->configData);

        $this->configData
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1, $arg2) use ($configEntity) {
                if ($arg1 == 'scope' && $arg2 == ScopeInterface::SCOPE_STORES) {
                    return $this->configData;
                } elseif ($arg1 == 'path' && $arg2 == DesignInterface::XML_PATH_THEME_ID) {
                    return [$configEntity];
                }
            });

        $this->themeMock->expects($this->any())->method('getId')->willReturn(6);
        $this->themeMock->expects($this->any())->method('getThemePath')->willReturn($themePath);

        $this->scopeConfigWriter->expects($this->once())->method('delete');

        $this->scopeConfigWriter->expects($this->exactly(3))->method('save');

        $this->model->assignToStore($this->themeMock, [2, 3, 5]);
    }
}
