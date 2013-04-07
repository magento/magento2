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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test for Mage_DesignEditor_Model_Observer
 */
class Mage_DesignEditor_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $area
     * @param string $designMode
     * @param array $expectedAssets
     *
     * @magentoAppIsolation enabled
     * @dataProvider cleanJsDataProvider
     */
    public function testCleanJs($area, $designMode, $expectedAssets)
    {
        $layout = Mage::app()->getLayout();
        /** @var $headBlock Mage_Page_Block_Html_Head */
        $headBlock = $layout->createBlock('Mage_Page_Block_Html_Head', 'head');
        $headBlock->setData('vde_design_mode', $designMode);

        $objectManager = Mage::getObjectManager();

        /** @var $page Mage_Core_Model_Page */
        $page = $objectManager->get('Mage_Core_Model_Page');

        /** @var $pageAssets Mage_Page_Model_Asset_GroupedCollection */
        $pageAssets = $page->getAssets();

        $fixtureAssets = array(
            array('name'   => 'test_css', 'type' => Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS,
                  'params' => array()),
            array('name'   => 'test_css_vde', 'type' => Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS,
                  'params' => array('flag_name' => 'vde_design_mode')),
            array('name'   => 'test_js', 'type' => Mage_Core_Model_Design_Package::CONTENT_TYPE_JS,
                  'params' => array()),
            array('name'   => 'test_js_vde', 'type' => Mage_Core_Model_Design_Package::CONTENT_TYPE_JS,
                  'params' => array('flag_name' => 'vde_design_mode')),
        );

        foreach ($fixtureAssets as $asset) {
            $pageAssets->add(
                $asset['name'],
                $objectManager->create('Mage_Core_Model_Page_Asset_ViewFile', array(
                    'file' => 'some_file',
                    'contentType' => $asset['type'],
                )),
                $asset['params']
            );
        }

        /** @var $eventManager Mage_Core_Model_Event_Manager */
        $eventManager = $objectManager->get('Mage_Core_Model_Event_Manager')->addEventArea($area);
        $eventManager->dispatch('controller_action_layout_generate_blocks_after', array('layout' => $layout));

        $actualAssets = array_keys($pageAssets->getAll());
        $this->assertEquals($expectedAssets, $actualAssets);
    }

    /**
     * @return array
     */
    public function cleanJsDataProvider()
    {
        return array(
            'vde area - design mode' => array('vde', '1', array('test_css', 'test_css_vde', 'test_js_vde')),
            'vde area - non design mode' => array('vde', '0',
                array('test_css', 'test_css_vde', 'test_js', 'test_js_vde')),
            'default area - design mode' => array('default', '1',
                array('test_css', 'test_css_vde', 'test_js', 'test_js_vde')),
            'default area - non design mode' => array('default', '0',
                array('test_css', 'test_css_vde', 'test_js', 'test_js_vde')),
        );
    }
}
