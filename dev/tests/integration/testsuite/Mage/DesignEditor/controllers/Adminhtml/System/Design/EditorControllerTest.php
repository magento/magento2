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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Adminhtml_System_Design_EditorControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * Identifier theme
     *
     * @var int
     */
    protected static $_themeId;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_dataHelper;

    public function setUp()
    {
        parent::setUp();
        $this->_dataHelper = $this->_objectManager->get('Mage_Core_Helper_Data');
    }

    /**
     * Create theme is db
     */
    public static function prepareTheme()
    {
        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme->setData(array(
            'theme_code'           => 'default',
            'package_code'         => 'default',
            'area'                 => 'frontend',
            'parent_id'            => null,
            'theme_path'           => 'default/demo',
            'theme_version'        => '2.0.0.0',
            'theme_title'          => 'Default',
            'magento_version_from' => '2.0.0.0-dev1',
            'magento_version_to'   => '*',
            'is_featured'          => '0'
        ));
        $theme->save();
        self::$_themeId = $theme->getId();
    }

    /**
     * Delete theme from db
     */
    public static function prepareThemeRollback()
    {
        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme->load(self::$_themeId)->delete();
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_design_editor/index');
        $content = $this->getResponse()->getBody();

        $this->assertContains('Choose a theme to start with', $content);
        $this->assertContains('<div class="infinite_scroll">', $content);
        $this->assertContains("jQuery('.infinite_scroll').infinite_scroll", $content);
    }

    public function testLaunchActionSingleStoreWrongThemeId()
    {
        $wrongThemeId = 999;
        $this->getRequest()->setParam('theme_id', $wrongThemeId);
        $this->dispatch('backend/admin/system_design_editor/launch');
        $this->assertSessionMessages($this->equalTo(
            array('Theme "' . $wrongThemeId . '" was not found.')),
            Mage_Core_Model_Message::ERROR
        );
        $expected = 'http://localhost/index.php/backend/admin/system_design_editor/index/';
        $this->assertRedirect($this->stringStartsWith($expected));
    }

    /**
     * @param array $source
     * @param array $result
     * @param bool $isXml
     *
     * @dataProvider getLayoutUpdateActionDataProvider
     */
    public function testGetLayoutUpdateAction(array $source, array $result, $isXml = false)
    {
        $this->getRequest()->setPost($source);
        $this->dispatch('backend/admin/system_design_editor/getLayoutUpdate');
        $response = $this->_dataHelper->jsonDecode($this->getResponse()->getBody());

        // convert to XML string to the same format as in $result
        if ($isXml) {
            foreach ($response as $code => $data) {
                foreach ($data as $key => $value) {
                    $xml = new Varien_Simplexml_Element($value);
                    $response[$code][$key] = $xml->asNiceXml();
                }
            }
        }
        $this->assertEquals($result, $response);
    }

    /**
     * Data provider for testGetLayoutUpdateAction
     *
     * @return array
     */
    public function getLayoutUpdateActionDataProvider()
    {
        $correctXml = new Varien_Simplexml_Element('<?xml version="1.0" encoding="UTF-8"?><layout/>');
        $correctXml = $correctXml->asNiceXml();

        return array(
            'no history data' => array(
                '$source' => array(),
                '$result' => array(
                    Mage_Core_Model_Message::ERROR => array('Invalid post data')
                ),
            ),
            'correct data' => array(
                '$source' => array('historyData' => array(
                    array (
                        'handle'                => 'current_handle',
                        'type'                  => 'layout',
                        'element_name'          => 'tags_popular',
                        'action_name'           => 'move',
                        'destination_container' => 'content',
                        'destination_order'     => '1',
                        'origin_container'      => 'left',
                        'origin_order'          => '1',
                    ),
                    array (
                        'handle'                => 'current_handle',
                        'type'                  => 'layout',
                        'element_name'          => 'tags_popular',
                        'action_name'           => 'move',
                        'destination_container' => 'left',
                        'destination_order'     => '1',
                        'origin_container'      => 'content',
                        'origin_order'          => '1',
                    ),
                )),
                '$result' => array(
                    Mage_Core_Model_Message::SUCCESS => array($correctXml)
                ),
                '$isXml' => true,
            ),
        );
    }
}
