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
class Mage_DesignEditor_Model_SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Admin_Model_Session
     */
    protected static $_adminSession;

    /**
     * @var Mage_DesignEditor_Model_Session
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_DesignEditor_Model_Session();
    }

    public function testIsDesignEditorActiveFalse()
    {
        $this->assertFalse($this->_model->isDesignEditorActive());
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testIsDesignEditorActiveTrue()
    {
        $this->assertTrue($this->_model->isDesignEditorActive());
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     * @magentoConfigFixture current_store admin/security/session_lifetime 100
     */
    public function testIsDesignEditorActiveAdminSessionExpired()
    {
        $this->assertTrue($this->_model->isDesignEditorActive());
        $this->_model->setUpdatedAt(time() - 101);
        $this->assertFalse($this->_model->isDesignEditorActive());
    }

    /**
     * @magentoDataFixture loginAdmin
     */
    public function testActivateDesignEditor()
    {
        $this->assertFalse($this->_model->isDesignEditorActive());
        $this->_model->activateDesignEditor();
        $this->assertTrue($this->_model->isDesignEditorActive());
    }

    public static function loginAdmin()
    {
        self::$_adminSession = new Mage_Admin_Model_Session();
        self::$_adminSession->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
    }

    public static function loginAdminRollback()
    {
        self::$_adminSession->logout();
    }

    /**
     * @magentoDataFixture Mage/DesignEditor/_files/design_editor_active.php
     */
    public function testDeactivateDesignEditor()
    {
        $this->assertTrue($this->_model->isDesignEditorActive());
        $this->_model->deactivateDesignEditor();
        $this->assertFalse($this->_model->isDesignEditorActive());
    }

    public function testSetSkin()
    {
        $this->_model->setSkin('default/default/blank');
        $this->assertEquals('default/default/blank', $this->_model->getSkin());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testSetSkinWrongValue()
    {
        $this->_model->setSkin('wrong/skin/applied');
    }
}
