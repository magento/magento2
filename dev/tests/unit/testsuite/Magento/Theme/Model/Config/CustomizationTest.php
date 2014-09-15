<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme customization config model
 */
namespace Magento\Theme\Model\Config;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
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
            'Magento\Framework\StoreManagerInterface',
            array(),
            '',
            true,
            true,
            true,
            array('getStores')
        );
        $this->_designPackage = $this->getMockForAbstractClass(
            'Magento\Framework\View\DesignInterface',
            array(),
            '',
            true,
            true,
            true,
            array('getConfigurationDesignTheme')
        );
        $this->_themeCollection = $this->getMock(
            'Magento\Core\Model\Resource\Theme\Collection',
            array('filterThemeCustomizations', 'load'),
            array(),
            '',
            false
        );

        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );

        $collectionFactory->expects($this->any())->method('create')->will($this->returnValue($this->_themeCollection));

        $this->themeProviderMock = $this->getMock(
            '\Magento\Core\Model\Theme\ThemeProvider',
            array('getThemeCustomizations', 'getThemeByFullPath'),
            array($collectionFactory, $this->getMock('\Magento\Core\Model\ThemeFactory', array(), array(), '', false)),
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
            $this->returnValue(array($this->_getStore()))
        );

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeCustomizations'
        )->with(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->will(
            $this->returnValue(array($this->_getAssignedTheme(), $this->_getUnassignedTheme()))
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
            $this->returnValue(array($this->_getStore()))
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
            $this->returnValue(array($this->_getAssignedTheme(), $this->_getUnassignedTheme()))
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
            $this->returnValue(array($this->_getStore()))
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
            $this->returnValue(array($this->_getStore()))
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
            $this->returnValue(array($this->_getAssignedTheme(), $this->_getUnassignedTheme()))
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
        return new \Magento\Framework\Object(array('id' => 1, 'theme_path' => 'Magento/plushe'));
    }

    /**
     * @return \Magento\Framework\Object
     */
    protected function _getUnassignedTheme()
    {
        return new \Magento\Framework\Object(array('id' => 2, 'theme_path' => 'Magento/blank'));
    }

    /**
     * @return \Magento\Framework\Object
     */
    protected function _getStore()
    {
        return new \Magento\Framework\Object(array('id' => 55));
    }
}
