<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme customization config model
 */
namespace Magento\Theme\Test\Unit\Model\Config;

use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Model\Theme\StoreThemesResolverInterface;
use Magento\Theme\Model\Theme\ThemeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomizationTest extends TestCase
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DesignInterface|MockObject
     */
    protected $designPackage;

    /**
     * @var Customization
     */
    protected $model;

    /**
     * @var ThemeProvider|MockObject
     */
    protected $themeProviderMock;
    /**
     * @var StoreThemesResolverInterface|MockObject
     */
    private $storeThemesResolver;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->designPackage = $this->getMockBuilder(DesignInterface::class)->getMock();

        $this->themeProviderMock = $this->getMockBuilder(ThemeProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getThemeCustomizations', 'getThemeByFullPath'])
            ->getMock();

        $this->storeThemesResolver = $this->createMock(StoreThemesResolverInterface::class);

        $this->model = new Customization(
            $this->storeManager,
            $this->designPackage,
            $this->themeProviderMock,
            $this->storeThemesResolver
        );
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::getAssignedThemeCustomizations
     * @covers \Magento\Theme\Model\Config\Customization::hasThemeAssigned
     * @covers \Magento\Theme\Model\Config\Customization::_prepareThemeCustomizations
     * @covers \Magento\Theme\Model\Config\Customization::__construct
     */
    public function testGetAssignedThemeCustomizations()
    {
        $store = $this->getStore();
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$store]);

        $this->storeThemesResolver->expects($this->once())
            ->method('getThemes')
            ->with($store)
            ->willReturn([$this->getAssignedTheme()->getId()]);

        $this->themeProviderMock->expects($this->once())
            ->method('getThemeCustomizations')
            ->with(Area::AREA_FRONTEND)
            ->willReturn([$this->getAssignedTheme(), $this->getUnassignedTheme()]);

        $assignedThemes = $this->model->getAssignedThemeCustomizations();
        $this->assertArrayHasKey($this->getAssignedTheme()->getId(), $assignedThemes);
        $this->assertTrue($this->model->hasThemeAssigned());
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::getUnassignedThemeCustomizations
     * @covers \Magento\Theme\Model\Config\Customization::__construct
     */
    public function testGetUnassignedThemeCustomizations()
    {
        $store = $this->getStore();
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$store]);

        $this->storeThemesResolver->expects($this->once())
            ->method('getThemes')
            ->with($store)
            ->willReturn([$this->getAssignedTheme()->getId()]);

        $this->themeProviderMock->expects($this->once())
            ->method('getThemeCustomizations')
            ->with(Area::AREA_FRONTEND)
            ->willReturn([$this->getAssignedTheme(), $this->getUnassignedTheme()]);

        $unassignedThemes = $this->model->getUnassignedThemeCustomizations();
        $this->assertArrayHasKey($this->getUnassignedTheme()->getId(), $unassignedThemes);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::getStoresByThemes
     * @covers \Magento\Theme\Model\Config\Customization::__construct
     */
    public function testGetStoresByThemes()
    {
        $store = $this->getStore();
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$store]);

        $this->storeThemesResolver->expects($this->once())
            ->method('getThemes')
            ->with($store)
            ->willReturn([$this->getAssignedTheme()->getId()]);

        $stores = $this->model->getStoresByThemes();
        $this->assertArrayHasKey($this->getAssignedTheme()->getId(), $stores);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
     * @covers \Magento\Theme\Model\Config\Customization::_getConfigurationThemeId
     * @covers \Magento\Theme\Model\Config\Customization::__construct
     */
    public function testIsThemeAssignedToAnyStore()
    {
        $store = $this->getStore();
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$store]);

        $this->storeThemesResolver->expects($this->once())
            ->method('getThemes')
            ->with($store)
            ->willReturn([$this->getAssignedTheme()->getId()]);

        $this->themeProviderMock->expects($this->once())
            ->method('getThemeCustomizations')
            ->with(Area::AREA_FRONTEND)
            ->willReturn([$this->getAssignedTheme(), $this->getUnassignedTheme()]);

        $themeAssigned = $this->model->isThemeAssignedToStore($this->getAssignedTheme());
        $this->assertTrue($themeAssigned);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
     * @covers \Magento\Theme\Model\Config\Customization::_isThemeAssignedToSpecificStore
     */
    public function testIsThemeAssignedToConcreteStore()
    {
        $this->designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn($this->getAssignedTheme()->getId());

        $themeUnassigned = $this->model->isThemeAssignedToStore($this->getUnassignedTheme(), $this->getStore());
        $this->assertFalse($themeUnassigned);
    }

    /**
     * @return DataObject
     */
    protected function getAssignedTheme()
    {
        return new DataObject(['id' => 1, 'theme_path' => 'Magento/luma']);
    }

    /**
     * @return DataObject
     */
    protected function getUnassignedTheme()
    {
        return new DataObject(['id' => 2, 'theme_path' => 'Magento/blank']);
    }

    /**
     * @return StoreInterface|MockObject
     */
    protected function getStore()
    {
        return $this->createConfiguredMock(StoreInterface::class, ['getId' => 55]);
    }
}
