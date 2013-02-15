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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Cms_Controller_RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Cms_Controller_Router
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Cms_Controller_Router(
            Mage::getObjectManager()->get('Mage_Core_Controller_Varien_Action_Factory'),
            new Mage_Core_Model_Event_ManagerStub(
                $this->getMock('Mage_Core_Model_ObserverFactory', array(), array(), '', false),
                $this->getMock('Mage_Core_Model_Event_Config', array(), array(), '', false)
            )
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testMatch()
    {
        $request = new Mage_Core_Controller_Request_Http();
        //Open Node
        Mage::getObjectManager()->get('Mage_Core_Controller_Response_Http')
            ->headersSentThrowsException = Mage::$headersSentThrowsException;
        $request->setPathInfo('parent_node');
        $controller = $this->_model->match($request);
        $this->assertInstanceOf('Mage_Core_Controller_Varien_Action_Redirect', $controller);
    }
}

/**
 * Event manager stub
 */
class Mage_Core_Model_Event_ManagerStub extends Mage_Core_Model_Event_Manager
{
    /**
     * Stub dispatch event
     *
     * @param string $eventName
     * @param array $params
     * @return Mage_Core_Model_App|null
     */
    public function dispatch($eventName, array $params = array())
    {
        switch ($eventName) {
            case 'cms_controller_router_match_before' :
                $params['condition']->setRedirectUrl('http://www.example.com/');
                break;
        }

        return null;
    }
}
