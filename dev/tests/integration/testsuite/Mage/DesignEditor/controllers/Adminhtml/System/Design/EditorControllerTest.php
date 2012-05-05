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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Adminhtml_System_Design_EditorControllerTest extends Mage_Adminhtml_Utility_Controller
{
    /**
     * Assert that a page content contains the design editor form
     *
     * @param string $content
     */
    protected function _assertContainsDesignEditor($content)
    {
        $expectedFormAction = 'http://localhost/index.php/admin/system_design_editor/launch/';
        $this->assertContains('Visual Design Editor', $content);
        $this->assertContains('<form id="edit_form" action="' . $expectedFormAction, $content);
        $this->assertContains("editForm = new varienForm('edit_form'", $content);
        $this->assertContains('onclick="editForm.submit();"', $content);
    }

    /**
     * Skip the current test, if session identifier is not defined in the environment
     */
    public function _requireSessionId()
    {
        if (!$this->_session->getSessionId()) {
            $this->markTestSkipped('Test requires environment with non-empty session identifier.');
        }
    }

    public function testIndexActionSingleStore()
    {
        $this->dispatch('admin/system_design_editor/index');
        $this->_assertContainsDesignEditor($this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Mage/Core/_files/store.php
     */
    public function testIndexActionMultipleStores()
    {
        $this->dispatch('admin/system_design_editor/index');
        $responseBody = $this->getResponse()->getBody();
        $this->_assertContainsDesignEditor($responseBody);
        $this->assertContains('<select id="store_id" name="store_id"', $responseBody);
        $this->assertContains('<label for="store_id">Store View', $responseBody);
        $this->assertContains('Fixture Store</option>', $responseBody);
    }

    public function testLaunchActionSingleStore()
    {
        $session = new Mage_DesignEditor_Model_Session();
        $this->assertFalse($session->isDesignEditorActive());
        $this->dispatch('admin/system_design_editor/launch');
        $this->assertTrue($session->isDesignEditorActive());

        $this->_requireSessionId();
        $this->assertRedirect('http://localhost/index.php/?SID=' . $this->_session->getSessionId());
    }

    /**
     * @magentoDataFixture Mage/Core/_files/store.php
     * @magentoConfigFixture fixturestore_store web/unsecure/base_link_url http://example.com/
     */
    public function testLaunchActionMultipleStores()
    {
        $this->getRequest()->setParam('store_id', Mage::app()->getStore('fixturestore')->getId());

        $session = new Mage_DesignEditor_Model_Session();
        $this->assertFalse($session->isDesignEditorActive());
        $this->dispatch('admin/system_design_editor/launch');
        $this->assertTrue($session->isDesignEditorActive());

        $this->_requireSessionId();
        $this->assertRedirect(
            'http://example.com/index.php/?SID=' . $this->_session->getSessionId() . '&___store=fixturestore'
        );
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testExitAction()
    {
        $session = new Mage_DesignEditor_Model_Session();
        $this->assertTrue($session->isDesignEditorActive());
        $this->dispatch('admin/system_design_editor/exit');

        $this->assertFalse($session->isDesignEditorActive());
        $this->assertContains(
            '<script type="text/javascript">window.close();</script>',
            $this->getResponse()->getBody()
        );
    }
}
