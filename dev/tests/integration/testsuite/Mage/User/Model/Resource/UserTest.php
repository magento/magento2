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
class Mage_User_Model_Resource_UserTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_User_Model_Resource_User */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getResourceSingleton('Mage_User_Model_Resource_User');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * No node - no limitation
     */
    public function testCanCreateUserTrue()
    {
        $this->assertTrue($this->_model->canCreateUser());
    }

    /**
     * Explicit zero - don't allow creating
     *
     * @magentoConfigFixture limitations/admin_account 0
     */
    public function testCanCreateUserZero()
    {
        $this->assertFalse($this->_model->canCreateUser());
    }

    /**
     * Any other values - compare with users count
     *
     * @magentoConfigFixture limitations/admin_account 1
     */
    public function testCanCreateUserFalse()
    {
        $this->assertFalse($this->_model->canCreateUser());
    }

    public function testGetValidationRulesBeforeSave()
    {
        $rules = $this->_model->getValidationRulesBeforeSave();
        $this->assertInstanceOf('Zend_Validate_Interface', $rules);
    }
}
