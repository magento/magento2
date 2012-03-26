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

/**
 * @group module:Mage_DesignEditor
 */
class Mage_DesignEditor_EditorControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    public function testPreDispatchSession()
    {
        $this->dispatch('design/editor/page');
        $this->assert404NotFound();
    }

    /**
     * @param string $pageType
     * @param string $expectedMessage
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     * @dataProvider pageActionErrorDataProvider
     */
    public function testPageActionError($pageType, $expectedMessage)
    {
        $this->getRequest()->setParam('page_type', $pageType);
        $this->dispatch('design/editor/page');
        $this->assertEquals(503, $this->getResponse()->getHttpResponseCode());
        $this->assertStringMatchesFormat($expectedMessage, $this->getResponse()->getBody());
    }

    /**
     * @return array
     */
    public function pageActionErrorDataProvider()
    {
        return array(
            'no page type'      => array('', 'Invalid page type specified.'),
            'invalid page type' => array('1nvalid_type', 'Invalid page type specified.'),
            'non-existing type' => array('non_existing_type', 'Specified page type doesn\'t exist: %s'),
        );
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     * @dataProvider pageActionDataProvider
     *
     * @param string $pageType
     * @param string $requiredModule
     * @param bool $isVdeToolbarBug
     */
    public function testPageAction($pageType, $requiredModule)
    {
        if (!in_array($requiredModule, Magento_Test_Helper_Factory::getHelper('config')->getEnabledModules())) {
            $this->markTestSkipped("Test requires the module '$requiredModule' to be enabled.");
        }
        $this->getRequest()->setParam('page_type', $pageType);
        $this->dispatch('design/editor/page');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $controller = Mage::app()->getFrontController()->getAction();
        $this->assertInstanceOf('Mage_DesignEditor_EditorController', $controller);
        $this->assertRegExp(
            '/treeInstance\.select_node\(.*"' . preg_quote($pageType, '/') . '".*\)/U',
            $this->getResponse()->getBody(),
            'Page type control should maintain the selection of the current page type.'
        );
    }

    public function pageActionDataProvider()
    {
        return array(
            'Catalog Product View'             => array('catalog_product_view',            'Mage_Catalog'),
            'One Page Checkout Overview'       => array('checkout_onepage_review',         'Mage_Checkout'),
            'Paypal Express Review Details'    => array('paypal_express_review_details',   'Mage_Paypal'),
            'Paypal UK Express Review Details' => array('paypaluk_express_review_details', 'Mage_PaypalUk'),
        );
    }

    public function testGetFullActionName()
    {
        $this->dispatch('design/editor/page');
        $controller = Mage::app()->getFrontController()->getAction();
        $this->assertNotInstanceOf('Mage_DesignEditor_EditorController', $controller);
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testSkinAction()
    {
        $this->getRequest()->setParam('skin', 'default/default/blank');
        $this->dispatch('design/editor/skin');
        $this->assertRedirect();

        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $this->assertEquals('default/default/blank', $session->getSkin());
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testSkinActionWrongValue()
    {
        $this->getRequest()->setParam('skin', 'wrong/skin/applied');
        $this->dispatch('design/editor/skin');
        $this->assertRedirect();

        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $this->assertNotEquals('wrong/skin/applied', $session->getSkin());
    }

    public function testSkinActionNonActivatedEditor()
    {
        $this->getRequest()->setParam('skin', 'default/default/blank');
        $this->dispatch('design/editor/skin');
        $this->assert404NotFound();

        $session = Mage::getSingleton('Mage_DesignEditor_Model_Session');
        $this->assertNotEquals('default/default/blank', $session->getSkin());
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testSkinActionRedirectUrl()
    {
        $expectedRedirectUrl = 'http://localhost/index.php/path/to/redirect/?value=1#anchor';

        $this->getRequest()->setParam('skin', 'default/default/blank');
        $this->getRequest()->setParam(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED,
            Mage::helper('Mage_Core_Helper_Data')->urlEncode($expectedRedirectUrl)
        );
        $this->dispatch('design/editor/skin');
        $this->assertRedirect($expectedRedirectUrl);
    }
}
