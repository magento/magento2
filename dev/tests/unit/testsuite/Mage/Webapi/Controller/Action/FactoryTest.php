<?php
/**
 * Test action controller factory class.
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Action_FactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Controller_Action_Factory */
    protected $_factory;

    /** @var Magento_ObjectManager */
    protected $_objectManagerMock;

    protected function setUp()
    {
        /** Init all dependencies for SUT. */
        $this->_objectManagerMock = $this->getMockBuilder('Magento_ObjectManager')->disableOriginalConstructor()
            ->getMock();
        /** Init SUT. */
        $this->_factory = new Mage_Webapi_Controller_Action_Factory($this->_objectManagerMock);
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_factory);
        unset($this->_objectManagerMock);
        parent::tearDown();
    }


    /**
     * Test create action controller method.
     */
    public function testCreateActionController()
    {
        /** Create mock object of Mage_Webapi_Controller_ActionAbstract. */
        $actionController = $this->getMockBuilder('Mage_Webapi_Controller_ActionAbstract')
            ->disableOriginalConstructor()->getMock();
        /** Create request object. */
        $request = new Mage_Webapi_Controller_Request('SOAP');
        $this->_objectManagerMock->expects($this->once())->method('create')->will(
            $this->returnValue($actionController)
        );
        $this->_factory->createActionController('Mage_Webapi_Controller_ActionAbstract', $request);
    }

    /**
     * Test action controller method with exception.
     */
    public function testCreateActionControllerWithException()
    {
        /** Create object of class which is not instance of Mage_Webapi_Controller_ActionAbstract. */
        $wrongController = new Varien_Object();
        /** Create request object. */
        $request = new Mage_Webapi_Controller_Request('SOAP');
        /** Mock object manager create method to return wrong controller */
        $this->_objectManagerMock->expects($this->any())->method('create')->will($this->returnValue($wrongController));
        $this->setExpectedException(
            'InvalidArgumentException',
            'The specified class is not a valid API action controller.'
        );
        $this->_factory->createActionController('ClassName', $request);
    }
}
