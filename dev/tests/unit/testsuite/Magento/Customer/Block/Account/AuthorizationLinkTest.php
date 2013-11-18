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
 * @package     Magento_Customer
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Block\Account;

/**
 * Test class for \Magento\Customer\Block\Account\AuthorizationLink
 */
class AuthorizationLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Customer\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Customer\Block\Account\AuthorizationLink
     */
    protected $_block;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_session = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(array('isLoggedIn'))
            ->getMock();
        $this->_helper = $this->getMockBuilder('Magento\Customer\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(array('getLogoutUrl', 'getLoginUrl'))
            ->getMock();

        $context = $this->_objectManager->getObject('Magento\Core\Block\Template\Context');

        $context->getHelperFactory()->expects($this->any())->method('get')->will($this->returnValue($this->_helper));

        $this->_block = $this->_objectManager->getObject(
            'Magento\Customer\Block\Account\AuthorizationLink',
            array(
                'context' => $context,
                'session' => $this->_session,
            )
        );
    }

    public function testGetLabelLoggedIn()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->assertEquals('Log Out', $this->_block->getLabel());
    }

    public function testGetLabelLoggedOut()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->assertEquals('Log In', $this->_block->getLabel());
    }

    public function testGetHrefLoggedIn()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $this->_helper->expects($this->once())->method('getLogoutUrl')->will($this->returnValue('logout url'));

        $this->assertEquals('logout url', $this->_block->getHref());
    }

    public function testGetHrefLoggedOut()
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->_helper->expects($this->once())->method('getLoginUrl')->will($this->returnValue('login url'));

        $this->assertEquals('login url', $this->_block->getHref());
    }
}
