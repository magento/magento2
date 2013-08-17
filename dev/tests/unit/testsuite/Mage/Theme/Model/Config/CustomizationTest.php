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
 * @category    Magento
 * @package     Mage_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme customization config model
 */
class Mage_Theme_Model_Config_CustomizationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Mage_Core_Model_View_DesignInterface
     */
    protected $_designPackage;

    /**
     * @var Mage_Core_Model_Resource_Theme_Collection
     */
    protected $_themeCollection;

    /**
     * @var Mage_Theme_Model_Config_Customization
     */
    protected $_model;

    protected function setUp()
    {
        $this->_storeManager = $this->getMockForAbstractClass(
            'Mage_Core_Model_StoreManagerInterface', array(), '', true, true, true, array('getStores')
        );
        $this->_designPackage = $this->getMockForAbstractClass(
            'Mage_Core_Model_View_DesignInterface', array(), '', true, true, true,
            array('getConfigurationDesignTheme')
        );
        $this->_themeCollection = $this->getMock(
            'Mage_Core_Model_Resource_Theme_Collection', array('filterThemeCustomizations', 'load'), array(), '', false
        );

        $collectionFactory = $this->getMock(
            'Mage_Core_Model_Resource_Theme_CollectionFactory', array('create'), array(), '', false
        );

        $collectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_themeCollection));

        $itemsProperty = new ReflectionProperty($this->_themeCollection, '_items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue(
            $this->_themeCollection, array($this->_getAssignedTheme(), $this->_getUnassignedTheme())
        );

        $this->_designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->will($this->returnValue($this->_getAssignedTheme()->getId()));

        $this->_model = new Mage_Theme_Model_Config_Customization(
            $this->_storeManager,
            $this->_designPackage,
            $collectionFactory
        );
    }

    protected function tearDown()
    {
        $this->_storeManager    = null;
        $this->_designPackage   = null;
        $this->_themeCollection = null;
        $this->_model           = null;
    }

    /**
     * @covers Mage_Theme_Model_Config_Customization::getAssignedThemeCustomizations
     */
    public function testGetAssignedThemeCustomizations()
    {
        $this->_themeCollection->expects($this->once())->method('load')->will(
            $this->returnValue(array($this->_getAssignedTheme(), $this->_getUnassignedTheme()))
        );

        $this->_storeManager->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue(array($this->_getStore())));

        $assignedThemes = $this->_model->getAssignedThemeCustomizations();
        $this->assertArrayHasKey($this->_getAssignedTheme()->getId(), $assignedThemes);
    }

    /**
     * @covers Mage_Theme_Model_Config_Customization::getUnassignedThemeCustomizations
     */
    public function testGetUnassignedThemeCustomizations()
    {
        $this->_themeCollection->expects($this->once())->method('load')->will(
            $this->returnValue(array($this->_getAssignedTheme(), $this->_getUnassignedTheme()))
        );

        $this->_storeManager->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue(array($this->_getStore())));

        $unassignedThemes = $this->_model->getUnassignedThemeCustomizations();
        $this->assertArrayHasKey($this->_getUnassignedTheme()->getId(), $unassignedThemes);
    }

    /**
     * @covers Mage_Theme_Model_Config_Customization::getStoresByThemes
     */
    public function testGetStoresByThemes()
    {
        $this->_storeManager->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue(array($this->_getStore())));

        $stores = $this->_model->getStoresByThemes();
        $this->assertArrayHasKey($this->_getAssignedTheme()->getId(), $stores);
    }

    /**
     * @covers Mage_Theme_Model_Config_Customization::isThemeAssignedToStore
     */
    public function testIsThemeAssignedToDefaultStore()
    {
        $this->_themeCollection->expects($this->once())->method('load')->will(
            $this->returnValue(array($this->_getAssignedTheme(), $this->_getUnassignedTheme()))
        );

        $this->_storeManager->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue(array($this->_getStore())));

        $themeAssigned = $this->_model->isThemeAssignedToStore($this->_getAssignedTheme());
        $this->assertEquals(true, $themeAssigned);
    }

    /**
     * @covers Mage_Theme_Model_Config_Customization::isThemeAssignedToStore
     */
    public function testIsThemeAssignedToConcreteStore()
    {
        $themeUnassigned = $this->_model->isThemeAssignedToStore($this->_getUnassignedTheme(), $this->_getStore());
        $this->assertEquals(false, $themeUnassigned);
    }

    /**
     * @return Varien_Object
     */
    protected function _getAssignedTheme()
    {
        return new Varien_Object(array('id' => 1));
    }

    /**
     * @return Varien_Object
     */
    protected function _getUnassignedTheme()
    {
        return new Varien_Object(array('id' => 2));
    }

    /**
     * @return Varien_Object
     */
    protected function _getStore()
    {
        return new Varien_Object(array('id' => 55));
    }
}
