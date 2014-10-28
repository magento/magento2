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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Contact\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Contact\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerViewHelper;

    public function setUp()
    {
        $this->_scopeConfig = $this->getMockForAbstractClass(
            '\Magento\Framework\App\Config\ScopeConfigInterface', ['getValue'], '', false
        );
        $this->_customerSession = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->_customerViewHelper = $this->getMock('\Magento\Customer\Helper\View', [], [], '', false);

        $this->_helper = new Data(
            $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false),
            $this->_scopeConfig,
            $this->_customerSession,
            $this->_customerViewHelper
        );
    }

    public function testIsEnabled()
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('1'));

        $this->assertTrue(is_string($this->_helper->isEnabled()));
    }

    public function testIsNotEnabled()
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(null));

        $this->assertTrue(is_null($this->_helper->isEnabled()));
    }

    public function testGetUserNameNotLoggedIn()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->assertEmpty($this->_helper->getUserName());
    }

    public function testGetUserName()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $objectBuilder = $this->getMockForAbstractClass(
            '\Magento\Framework\Service\Data\AbstractSimpleObjectBuilder',
            ['getData'],
            '',
            false
        );
        $customerDataObject = new \Magento\Customer\Service\V1\Data\Customer($objectBuilder);
        $this->_customerSession->expects($this->once())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObject));

        $this->_customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->will($this->returnValue(' customer name '));

        $this->assertEquals('customer name', $this->_helper->getUserName());
    }

    public function testGetUserEmailNotLoggedIn()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->assertEmpty($this->_helper->getUserEmail());
    }

    public function testGetUserEmail()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $customerDataObject = $this->getMock('\Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $customerDataObject->expects($this->once())->method('getEmail')->will($this->returnValue('customer@email.com'));
        $this->_customerSession->expects($this->once())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObject));

        $this->assertEquals('customer@email.com', $this->_helper->getUserEmail());
    }
}
