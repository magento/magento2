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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * A parent class for adminhtml controllers - contains directives for admin user creation and logging in
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.numberOfChildren)
 */
class Mage_Adminhtml_Utility_Controller extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var Mage_Admin_Model_Session
     */
    protected $_session;

    protected function setUp()
    {
        parent::setUp();

        Mage::getSingleton('Mage_Adminhtml_Model_Url')->turnOffSecretKey();

        $this->_session = new Mage_Admin_Model_Session();
        $this->_session->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
    }

    protected function tearDown()
    {
        $this->_session->logout();
        Mage::getSingleton('Mage_Adminhtml_Model_Url')->turnOnSecretKey();

        parent::tearDown();
    }
}
