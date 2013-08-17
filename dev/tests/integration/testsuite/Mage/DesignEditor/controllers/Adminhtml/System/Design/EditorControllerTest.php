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

/**
 * @magentoAppArea adminhtml
 */
class Mage_DesignEditor_Adminhtml_System_Design_EditorControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_dataHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->_dataHelper = $this->_objectManager->get('Mage_Core_Helper_Data');
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_design_editor/index');
        $content = $this->getResponse()->getBody();

        $this->assertContains('<div class="infinite_scroll">', $content);
        $this->assertContains("jQuery('.infinite_scroll').infinite_scroll", $content);
    }

    public function testLaunchActionSingleStoreWrongThemeId()
    {
        $wrongThemeId = 999;
        $this->getRequest()->setParam('theme_id', $wrongThemeId);
        $this->dispatch('backend/admin/system_design_editor/launch');
        $this->assertSessionMessages($this->equalTo(
            array('We can\'t find theme "' . $wrongThemeId . '".')),
            Mage_Core_Model_Message::ERROR
        );
        $expected = 'http://localhost/index.php/backend/admin/system_design_editor/index/';
        $this->assertRedirect($this->stringStartsWith($expected));
    }
}
