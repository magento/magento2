<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme customization config model
 */
namespace Magento\Theme\Model\Config;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_designPackage;

    /**
     * @var \Magento\Core\Model\Resource\Theme\Collection
     */
    protected $_themeCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_model;

    /**
     * @var \Magento\Core\Model\Theme\ThemeProvider|\PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $themeProviderMock;

    protected function setUp()
    {
        $this->_storeManager = $this->getMockForAbstractClass(
            'Magento\Store\Model\StoreManagerInterface',
            [],
            '',
            true,
            true,
            true,
            ['getStores']
        );
        $this->_designPackage = $this->getMockForAbstractClass(
            'Magento\Framework\View\DesignInterface',
            [],
            '',
            true,
            true,
            true,
            ['getConfigurationDesignTheme']
        );
        $this->_themeCollection = $this->getMock(
            'Magento\Core\Model\Resource\Theme\Collection',
            ['filterThemeCustomizations', 'load'],
            [],
            '',
            false
        );

        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->_themeCollection));

        $this->themeProviderMock = $this->getMock(
            '\Magento\Core\Model\Theme\ThemeProvider',
            ['getThemeCustomizations', 'getThemeByFullPath'],
            [$collectionFactory, $this->getMock('\Magento\Core\Model\ThemeFactory', [], [], '', false)],
            '',
            false
        );

        $this->_model = new \Magento\Theme\Model\Config\Customization(
            $this->_storeManager,
            $this->_designPackage,
            $this->themeProviderMock
        );
    }

    protected function tearDown()
    {
        $this->_storeManager = null;
        $this->_designPackage = null;
        $this->_themeCollection = null;
        $this->_model = null;
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::getAssignedThemeCustomizations
     */
    public function testGetAssignedThemeCustomizations()
    {
        $this->_designPackage->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue($this->_getAssignedTheme()->getId())
        );

        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getStores'
        )->will(
            $this->returnValue([$this->_getStore()])
        );

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeCustomizations'
        )->with(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->will(
            $this->returnValue([$this->_getAssignedTheme(), $this->_getUnassignedTheme()])
        );

        $assignedThemes = $this->_model->getAssignedThemeCustomizations();
        $this->assertArrayHasKey($this->_getAssignedTheme()->getId(), $assignedThemes);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::getUnassignedThemeCustomizations
     */
    public function testGetUnassignedThemeCustomizations()
    {
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getStores'
        )->will(
            $this->returnValue([$this->_getStore()])
        );

        $this->_designPackage->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue($this->_getAssignedTheme()->getId())
        );

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeCustomizations'
        )->with(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->will(
            $this->returnValue([$this->_getAssignedTheme(), $this->_getUnassignedTheme()])
        );

        $unassignedThemes = $this->_model->getUnassignedThemeCustomizations();
        $this->assertArrayHasKey($this->_getUnassignedTheme()->getId(), $unassignedThemes);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::getStoresByThemes
     */
    public function testGetStoresByThemes()
    {
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getStores'
        )->will(
            $this->returnValue([$this->_getStore()])
        );

        $this->_designPackage->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue($this->_getAssignedTheme()->getId())
        );

        $stores = $this->_model->getStoresByThemes();
        $this->assertArrayHasKey($this->_getAssignedTheme()->getId(), $stores);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
     */
    public function testIsThemeAssignedToDefaultStore()
    {
        $this->_storeManager->expects(
            $this->once()
        )->method(
            'getStores'
        )->will(
            $this->returnValue([$this->_getStore()])
        );

        $this->_designPackage->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue($this->_getAssignedTheme()->getId())
        );

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeCustomizations'
        )->with(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->will(
            $this->returnValue([$this->_getAssignedTheme(), $this->_getUnassignedTheme()])
        );

        $themeAssigned = $this->_model->isThemeAssignedToStore($this->_getAssignedTheme());
        $this->assertEquals(true, $themeAssigned);
    }

    /**
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
     */
    public function testIsThemeAssignedToConcreteStore()
    {
        $this->_designPackage->expects(
            $this->once()
        )->method(
            'getConfigurationDesignTheme'
        )->will(
            $this->returnValue($this->_getAssignedTheme()->getId())
        );

        $themeUnassigned = $this->_model->isThemeAssignedToStore($this->_getUnassignedTheme(), $this->_getStore());
        $this->assertEquals(false, $themeUnassigned);
    }

    /**
     * @return \Magento\Framework\Object
     */
    protected function _getAssignedTheme()
    {
        return new \Magento\Framework\Object(['id' => 1, 'theme_path' => 'Magento/luma']);
    }

    /**
     * @return \Magento\Framework\Object
     */
    protected function _getUnassignedTheme()
    {
        return new \Magento\Framework\Object(['id' => 2, 'theme_path' => 'Magento/blank']);
    }

    /**
     * @return \Magento\Framework\Object
     */
    protected function _getStore()
    {
        return new \Magento\Framework\Object(['id' => 55]);
    }
}
