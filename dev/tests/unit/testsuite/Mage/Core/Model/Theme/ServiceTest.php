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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme service model
 */
class Mage_Core_Model_Theme_ServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Mage_Core_Model_Theme_Service::isPresentCustomizedThemes
     * @dataProvider isIsCustomizationsExistDataProvider
     * @param array $availableIsVirtual
     * @param bool $expectedResult
     */
    public function testIsCustomizationsExist($availableIsVirtual, $expectedResult)
    {
        $themeCollectionMock = array();
        foreach ($availableIsVirtual as $isVirtual) {
            /** @var $themeItemMock Mage_Core_Model_Theme */
            $themeItemMock = $this->getMock('Mage_Core_Model_Theme', array('isVirtual'), array(), '', false);
            $themeItemMock->expects($this->any())
                ->method('isVirtual')
                ->will($this->returnValue($isVirtual));
            $themeCollectionMock[] = $themeItemMock;
        }

        /** @var $themeMock Mage_Core_Model_Theme */
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('getCollection'), array(), '', false);
        $themeMock->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($themeCollectionMock));

        $themeFactoryMock = $this->getMock('Mage_Core_Model_Theme_Factory', array('create'), array(), '', false);
        $themeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($themeMock));

        $themeService = new Mage_Core_Model_Theme_Service($themeFactoryMock,
            $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_App', array(), array(), '', false),
            $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false),
            $this->getMock('Mage_DesignEditor_Model_Resource_Layout_Update', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false)
        );
        $this->assertEquals($expectedResult, $themeService->isCustomizationsExist());
    }

    /**
     * @return array
     */
    public function isIsCustomizationsExistDataProvider()
    {
        return array(
            array(array(false, false, false), false),
            array(array(false, true, false), true)
        );
    }

    /**
     * @covers Mage_Core_Model_Theme_Service::assignThemeToStores
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Theme is not recognized. Requested id: -1
     */
    public function testAssignThemeToStoresWrongThemeId()
    {
        /** @var $themeMock Mage_Core_Model_Theme */
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('load', 'getId'), array(), '', false);
        $themeMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(-1))
            ->will($this->returnValue($themeMock));

        $themeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(false));
        $themeFactoryMock = $this->getMock('Mage_Core_Model_Theme_Factory', array('create'), array(), '', false);
        $themeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($themeMock));

        $themeService = new Mage_Core_Model_Theme_Service($themeFactoryMock,
            $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_App', array(), array(), '', false),
            $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false),
            $this->getMock('Mage_DesignEditor_Model_Resource_Layout_Update', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false)
        );
        $themeService->assignThemeToStores(-1);
    }

    /**
     * @covers Mage_Core_Model_Theme_Service::getAssignedThemeCustomizations
     * @covers Mage_Core_Model_Theme_Service::getUnassignedThemeCustomizations
     * @dataProvider getAssignedUnassignedThemesDataProvider
     * @param array $stores
     * @param array $themes
     * @param array $expAssignedThemes
     * @param array $expUnassignedThemes
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testGetAssignedAndUnassignedThemes($stores, $themes, $expAssignedThemes, $expUnassignedThemes)
    {
        $index = 0;
        /* Mock assigned themeId to each store */
        $storeConfigMock = $this->getMock('Mage_Core_Model_Store', array('getId'), array(), '', false);
        $storeMockCollection = array();
        foreach ($stores as $thisId) {
            $storeConfigMock->expects($this->at($index++))
                ->method('getId')
                ->will($this->returnValue($thisId));

            $storeMockCollection[] = $storeConfigMock;
        }

        /* Mock list existing stores */
        $appMock = $this->getMock('Mage_Core_Model_App', array('getStores'), array(), '', false);
        $appMock->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue($storeMockCollection));

        /* Mock list customized themes */
        $themesMock = array();
        foreach ($themes as $themeId) {
            /** @var $theme Mage_Core_Model_Theme */
            $theme = $this->getMock('Mage_Core_Model_Theme', array('getId'), array(), '', false);
            $theme->expects($this->any())->method('getId')->will($this->returnValue($themeId));
            $themesMock[] = $theme;
        }

        $designMock = $this->getMock('Mage_Core_Model_Design_Package', array('getConfigurationDesignTheme'),
            array(), '', false);
        $designMock->expects($this->any())
            ->method('getConfigurationDesignTheme')
            ->with($this->anything(), $this->arrayHasKey('store'))
            ->will($this->returnCallback(
                function($area, $params) {
                    return $params['store']->getId();
                }
            ));
        $helperMock = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);
        $layoutUpdateMock = $this->getMock('Mage_DesignEditor_Model_Resource_Layout_Update', array(), array(), '',
            false
        );
        $eventManagerMock = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '',
            false
        );

        $themeFactoryMock = $this->getMock('Mage_Core_Model_Theme_Factory', array('create'), array(), '', false);
        $themeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMock('Mage_Core_Model_Theme', array(), array(), '', false)));

        /** @var $themeService Mage_Core_Model_Theme_Service */
        $themeService = $this->getMock('Mage_Core_Model_Theme_Service', array('_getThemeCustomizations'),
            array($themeFactoryMock, $designMock, $appMock, $helperMock, $layoutUpdateMock, $eventManagerMock));
        $themeService->expects($this->once())
            ->method('_getThemeCustomizations')
            ->will($this->returnValue($themesMock));

        $assignedThemes = $themeService->getAssignedThemeCustomizations();
        $unassignedThemes = $themeService->getUnassignedThemeCustomizations();

        $assignedData = array();
        foreach ($assignedThemes as $theme) {
            $assignedData[$theme->getId()] = $theme->getAssignedStores();
        }
        $this->assertEquals(array_keys($expAssignedThemes), array_keys($assignedData));


        $unassignedData = array();
        foreach ($unassignedThemes as $theme) {
            $unassignedData[] = $theme->getId();
        }
        $this->assertEquals($expUnassignedThemes, $unassignedData);
    }

    /**
     * @return array()
     */
    public function getAssignedUnassignedThemesDataProvider()
    {
        return array(
            array(
                'stores' => array(
                    'store_1' => 1,
                    'store_2' => 4,
                    'store_3' => 3,
                    'store_4' => 8,
                    'store_5' => 3,
                    'store_6' => 10,
                ),
                'themes' => array(1, 2, 3, 4, 5, 6, 7, 8, 9),
                'expectedAssignedThemes' => array(
                    1 => array('store_1'),
                    3 => array('store_3', 'store_5'),
                    4 => array('store_2'),
                    8 => array('store_4'),
                ),
                'expectedUnassignedThemes' => array(2, 5, 6, 7, 9)
            )
        );
    }
}
