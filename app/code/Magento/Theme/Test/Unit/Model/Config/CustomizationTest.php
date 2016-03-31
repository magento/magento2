<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme customization config model
 */
namespace Magento\Theme\Test\Unit\Model\Config;

use Magento\Framework\App\Area;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designPackage;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\Collection
     */
    protected $themeCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $model;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeProvider|\PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $themeProviderMock;

    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')->getMock();
        $this->designPackage = $this->getMockBuilder('Magento\Framework\View\DesignInterface')->getMock();
        $this->themeCollection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Theme\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactory = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Theme\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->themeCollection));

        $this->themeProviderMock = $this->getMockBuilder('\Magento\Theme\Model\Theme\ThemeProvider')
            ->disableOriginalConstructor()
            ->setMethods(['getThemeCustomizations', 'getThemeByFullPath'])
            ->getMock();

        $this->model = new \Magento\Theme\Model\Config\Customization(
            $this->storeManager,
            $this->designPackage,
            $this->themeProviderMock
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
        $this->designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn($this->getAssignedTheme()->getId());

        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->getStore()]);

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
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->getStore()]);

        $this->designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn($this->getAssignedTheme()->getId());

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
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->getStore()]);

        $this->designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn($this->getAssignedTheme()->getId());

        $stores = $this->model->getStoresByThemes();
        $this->assertArrayHasKey($this->getAssignedTheme()->getId(), $stores);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
     * @covers \Magento\Theme\Model\Config\Customization::_getConfigurationThemeId
     * @covers \Magento\Theme\Model\Config\Customization::__construct
     */
    public function testIsThemeAssignedToDefaultStore()
    {
        $this->storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$this->getStore()]);

        $this->designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn($this->getAssignedTheme()->getId());

        $this->themeProviderMock->expects($this->once())
            ->method('getThemeCustomizations')
            ->with(Area::AREA_FRONTEND)
            ->willReturn([$this->getAssignedTheme(), $this->getUnassignedTheme()]);

        $themeAssigned = $this->model->isThemeAssignedToStore($this->getAssignedTheme());
        $this->assertEquals(true, $themeAssigned);
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
        $this->assertEquals(false, $themeUnassigned);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function getAssignedTheme()
    {
        return new \Magento\Framework\DataObject(['id' => 1, 'theme_path' => 'Magento/luma']);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function getUnassignedTheme()
    {
        return new \Magento\Framework\DataObject(['id' => 2, 'theme_path' => 'Magento/blank']);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function getStore()
    {
        return new \Magento\Framework\DataObject(['id' => 55]);
    }
}
