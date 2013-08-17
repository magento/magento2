<?php
/**
 * Test Mage_Webapi_Model_Authorization
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
class Mage_Webapi_Model_AuthorizationTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_coreAuthorization;

    /** @var Mage_Webapi_Helper_Data */
    protected $_helperMock;

    /** @var Mage_Webapi_Model_Authorization */
    protected $_webapiAuthorization;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_coreAuthorization = $this->getMockBuilder('Magento_AuthorizationInterface')
            ->getMock();
        $this->_helperMock = $this->getMockBuilder('Mage_Webapi_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('__'))
            ->getMock();
        /** Initialize SUT. */
        $this->_webapiAuthorization = new Mage_Webapi_Model_Authorization(
            $this->_helperMock,
            $this->_coreAuthorization
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_coreAuthorization);
        unset($this->_helperMock);
        unset($this->_webapiAuthorization);
        parent::tearDown();
    }

    public function testCheckResourceAclMageWebapiException()
    {
        $this->_coreAuthorization->expects($this->exactly(2))->method('isAllowed')->will($this->returnValue(false));
        $this->_helperMock->expects($this->once())->method('__')->will($this->returnArgument(0));
        $this->setExpectedException('Mage_Webapi_Exception', 'Access to resource is forbidden.');
        $this->_webapiAuthorization->checkResourceAcl('invalidResource', 'invalidMethod');
    }

    public function testCheckResourceAcl()
    {
        $this->_coreAuthorization->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $this->_webapiAuthorization->checkResourceAcl('validResource', 'validMethod');
    }
}
