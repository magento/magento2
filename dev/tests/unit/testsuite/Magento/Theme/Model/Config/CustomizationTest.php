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
 * @package     Magento_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme customization config model
 */
namespace Magento\Theme\Model\Config;

class CustomizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\View\DesignInterface
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

    protected function setUp()
    {
        $this->_storeManager = $this->getMockForAbstractClass(
            'Magento\Core\Model\StoreManagerInterface', array(), '', true, true, true, array('getStores')
        );
        $this->_designPackage = $this->getMockForAbstractClass(
            'Magento\View\DesignInterface', array(), '', true, true, true,
            array('getConfigurationDesignTheme')
        );
        $this->_themeCollection = $this->getMock(
            'Magento\Core\Model\Resource\Theme\Collection',
            array('filterThemeCustomizations', 'load'), array(), '', false
        );

        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory', array('create'), array(), '', false
        );

        $collectionFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_themeCollection));

        $itemsProperty = new \ReflectionProperty($this->_themeCollection, '_items');
        $itemsProperty->setAccessible(true);
        $itemsProperty->setValue(
            $this->_themeCollection, array($this->_getAssignedTheme(), $this->_getUnassignedTheme())
        );

        $this->_designPackage->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->will($this->returnValue($this->_getAssignedTheme()->getId()));

        $this->_model = new \Magento\Theme\Model\Config\Customization(
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
     * @covers \Magento\Theme\Model\Config\Customization::getAssignedThemeCustomizations
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
     * @covers \Magento\Theme\Model\Config\Customization::getUnassignedThemeCustomizations
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
     * @covers \Magento\Theme\Model\Config\Customization::getStoresByThemes
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
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
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
     * @covers \Magento\Theme\Model\Config\Customization::isThemeAssignedToStore
     */
    public function testIsThemeAssignedToConcreteStore()
    {
        $themeUnassigned = $this->_model->isThemeAssignedToStore($this->_getUnassignedTheme(), $this->_getStore());
        $this->assertEquals(false, $themeUnassigned);
    }

    /**
     * @return \Magento\Object
     */
    protected function _getAssignedTheme()
    {
        return new \Magento\Object(array('id' => 1));
    }

    /**
     * @return \Magento\Object
     */
    protected function _getUnassignedTheme()
    {
        return new \Magento\Object(array('id' => 2));
    }

    /**
     * @return \Magento\Object
     */
    protected function _getStore()
    {
        return new \Magento\Object(array('id' => 55));
    }
}
