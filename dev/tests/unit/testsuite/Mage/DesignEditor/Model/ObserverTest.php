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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test number of days after which layout will be removed
     */
    const TEST_DAYS_TO_EXPIRE = 5;

    /**
     * @var Mage_DesignEditor_Model_Observer
     */
    protected $_model;

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @return array
     */
    public function setThemeDataProvider()
    {
        return array(
            'no theme id'      => array('$themeId' => null),
            'correct theme id' => array('$themeId' => 1),
        );
    }

    public function testClearLayoutUpdates()
    {
        // mocks
        $helper = $this->getMock('Mage_DesignEditor_Helper_Data', array('getDaysToExpire'), array(), '', false);
        $helper->expects($this->once())
            ->method('getDaysToExpire')
            ->will($this->returnValue(self::TEST_DAYS_TO_EXPIRE));

        /** @var $linkCollection Mage_Core_Model_Resource_Layout_Link_Collection */
        $linkCollection = $this->getMock(
            'Mage_Core_Model_Resource_Layout_Link_Collection',
            array('addTemporaryFilter', 'addUpdatedDaysBeforeFilter', 'load'),
            array(),
            '',
            false
        );
        $linkCollection->expects($this->once())
            ->method('addTemporaryFilter')
            ->with(true)
            ->will($this->returnSelf());
        $linkCollection->expects($this->once())
            ->method('addUpdatedDaysBeforeFilter')
            ->with(self::TEST_DAYS_TO_EXPIRE)
            ->will($this->returnSelf());
        for ($i = 0; $i < 3; $i++) {
            $link = $this->getMock('Mage_Core_Model_Layout_Link', array('delete'), array(), '', false);
            $link->expects($this->once())
                ->method('delete');
            $linkCollection->addItem($link);
        }

        /** @var $layoutCollection Mage_Core_Model_Resource_Layout_Update_Collection */
        $layoutCollection = $this->getMock(
            'Mage_Core_Model_Resource_Layout_Update_Collection',
            array('addNoLinksFilter', 'addUpdatedDaysBeforeFilter', 'load'),
            array(),
            '',
            false
        );
        $layoutCollection->expects($this->once())
            ->method('addNoLinksFilter')
            ->will($this->returnSelf());
        $layoutCollection->expects($this->once())
            ->method('addUpdatedDaysBeforeFilter')
            ->with(self::TEST_DAYS_TO_EXPIRE)
            ->will($this->returnSelf());
        for ($i = 0; $i < 3; $i++) {
            $layout = $this->getMock('Mage_Core_Model_Layout_Update', array('delete'), array(), '', false);
            $layout->expects($this->once())
                ->method('delete');
            $layoutCollection->addItem($layout);
        }

        $objectManager = $this->getMock('Magento_ObjectManager');
        $objectManager->expects($this->at(0))
            ->method('create')
            ->with('Mage_Core_Model_Resource_Layout_Link_Collection')
            ->will($this->returnValue($linkCollection));
        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Mage_Core_Model_Resource_Layout_Update_Collection')
            ->will($this->returnValue($layoutCollection));

        $cacheManager = $this->getMock('Mage_Core_Model_CacheInterface', array(), array(), '', false);

        // test
        $this->_model = new Mage_DesignEditor_Model_Observer($objectManager, $helper, $cacheManager);
        $this->_model->clearLayoutUpdates();
    }

    public function testSaveQuickStyles()
    {
        $generatedContent = 'generated css content';

        $cacheManager = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false);
        $objectManager = $this->getMock('Magento_ObjectManager');
        $helper = $this->getMock('Mage_DesignEditor_Helper_Data', array(), array(), '', false);

        /** Prepare renderer */
        $renderer = $this->getMock(
            'Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer', array('render'), array(), '', false
        );

        $renderer->expects($this->once())
            ->method('render')
            ->will($this->returnValue($generatedContent));

        /** Prepare CSS */
        $cssFile = $this->getMock(
            'Mage_Core_Model_Theme_Customization_Files_Css', array('setDataForSave'), array(), '', false
        );
        $cssFile->expects($this->once())
            ->method('setDataForSave')
            ->with(array(Mage_Core_Model_Theme_Customization_Files_Css::QUICK_STYLE_CSS => $generatedContent))
            ->will($this->returnValue($renderer));

        /** Prepare theme */
        $theme = $this->getMock('Mage_Core_Model_Theme', array('setCustomization', 'save'), array(), '', false);

        $theme->expects($this->once())
            ->method('setCustomization')
            ->with($cssFile)
            ->will($this->returnValue($theme));

        $theme->expects($this->once())
            ->method('save')
            ->will($this->returnValue($theme));

        /** Prepare configuration */
        $configuration = $this->getMock(
            'Mage_DesignEditor_Model_Editor_Tools_Controls_Configuration',
            array('getControlConfig', 'getTheme'), array(), '', false
        );
        $quickStyle = $this->getMock(
            'Mage_DesignEditor_Model_Config_Control_QuickStyles', array(), array(), '', false
        );

        $configuration->expects($this->once())
            ->method('getControlConfig')
            ->will($this->returnValue($quickStyle));

        $configuration->expects($this->once())
            ->method('getTheme')
            ->will($this->returnValue($theme));

        /** Prepare event */
        $event = $this->getMock('Varien_Event_Observer', array('getData'), array(), '', false);

        $event->expects($this->once())
            ->method('getData')
            ->with('configuration')
            ->will($this->returnValue($configuration));

        /** Prepare observer */
        $objectManager->expects($this->at(0))
            ->method('create')
            ->with('Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer')
            ->will($this->returnValue($renderer));

        $objectManager->expects($this->at(1))
            ->method('create')
            ->with('Mage_Core_Model_Theme_Customization_Files_Css')
            ->will($this->returnValue($cssFile));

        $this->_model = new Mage_DesignEditor_Model_Observer($objectManager, $helper, $cacheManager);
        $this->_model->saveQuickStyles($event);
    }
}
