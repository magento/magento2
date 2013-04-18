<?php
/**
 * Test Response factory.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Response_FactoryTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Controller_Response_Factory */
    protected $_factory;

    /** @var Mage_Webapi_Controller_Front */
    protected $_apiFrontController;

    /** @var Magento_ObjectManager */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_apiFrontController = $this->getMockBuilder('Mage_Webapi_Controller_Front')
            ->disableOriginalConstructor()->getMock();
        $this->_objectManager = $this->getMockBuilder('Magento_ObjectManager')->disableOriginalConstructor()
            ->getMock();
        $this->_factory = new Mage_Webapi_Controller_Response_Factory(
            $this->_apiFrontController,
            $this->_objectManager);
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_factory);
        unset($this->_apiFrontController);
        unset($this->_objectManager);
        parent::tearDown();
    }

    /**
     * Test GET method.
     */
    public function testGet()
    {
        /** Mock front controller mock to return SOAP API type. */
        $this->_apiFrontController->expects($this->once())->method('determineApiType')->will(
            $this->returnValue(Mage_Webapi_Controller_Front::API_TYPE_SOAP)
        );
        /** Assert that object manager get() will be executed once with Mage_Webapi_Controller_Response parameter. */
        $this->_objectManager->expects($this->once())->method('get')->with('Mage_Webapi_Controller_Response');
        $this->_factory->get();
    }

    /**
     * Test GET method with wrong API type.
     */
    public function testGetWithWrongApiType()
    {
        $wrongApiType = 'Wrong SOAP';
        /**Mock front controller determine API method to return wrong API type */
        $this->_apiFrontController->expects($this->once())->method('determineApiType')->will(
            $this->returnValue($wrongApiType)
        );
        $this->setExpectedException(
            'LogicException',
            sprintf('There is no corresponding response class for the "%s" API type.', $wrongApiType)
        );
        $this->_factory->get();
    }
}
