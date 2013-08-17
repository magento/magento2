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
 * @package     Mage_Customer
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require 'Mage/Customer/controllers/AccountController.php';
/**
 * Test customer account controller
 */
class Mage_Customer_AccountControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * List of actions that are allowed for not authorized users
     *
     * @var array
     */
    protected $_openActions = array(
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
        'createpassword',
        'createpost',
        'loginpost'
    );

    /**
     * @var Mage_Customer_AccountController
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments('Mage_Customer_AccountController');
        $this->_model = $objectManagerHelper->getObject('Mage_Customer_AccountController', $constructArguments);
    }

    /**
     * @covers Mage_Customer_AccountController::_getAllowedActions
     */
    public function testGetAllowedActions()
    {
        $this->assertAttributeEquals($this->_openActions, '_openActions', $this->_model);

        $method = new ReflectionMethod('Mage_Customer_AccountController', '_getAllowedActions');
        $method->setAccessible(true);
        $this->assertEquals($this->_openActions, $method->invoke($this->_model));
    }
}
