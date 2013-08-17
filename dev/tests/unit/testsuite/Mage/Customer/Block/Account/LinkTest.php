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

class Mage_Customer_Block_Account_LinkTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Mage_Customer_Model_Session */
    protected $_session;

    /** @var PHPUnit_Framework_MockObject_MockObject|Mage_Customer_Helper_Data */
    protected $_helper;

    /** @var PHPUnit_Framework_MockObject_MockObject|Mage_Page_Block_Template_Links */
    protected $_targetBlock;

    /** @var Mage_Customer_Block_Account_Link */
    protected $_block;

    public function setUp()
    {
        $this->_session = $this->getMock('Mage_Customer_Model_Session', array(), array(), '', false);

        $this->_helper = $this->getMock('Mage_Customer_Helper_Data', array(), array(), '', false);

        $helperFactory = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $helperFactory->expects($this->any())
            ->method('get')
            ->with('Mage_Customer_Helper_Data')
            ->will($this->returnValue($this->_helper));

        $this->_targetBlock = $this->getMock('Mage_Page_Block_Template_Links', array(), array(), '', false);

        $layout = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $layout->expects($this->any())
            ->method('getBlock')
            ->with('target_block')
            ->will($this->returnValue($this->_targetBlock));

        $context = $this->getMock('Mage_Core_Block_Context', array(), array(), '', false);
        $context->expects($this->any())
            ->method('getHelperFactory')
            ->will($this->returnValue($helperFactory));
        $context->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($layout));

        $this->_block = new Mage_Customer_Block_Account_Link($context, $this->_session);
    }

    /**
     * @param bool $isLoggedIn
     * @param string $expectedUrlMethod
     * @dataProvider removeAuthLinkDataProvider
     */
    public function testRemoveAuthLink($isLoggedIn, $expectedUrlMethod)
    {
        $this->_session->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue($isLoggedIn));

        $this->_helper->expects($this->once())
            ->method($expectedUrlMethod)
            ->will($this->returnValue('composed_url'));

        $this->_targetBlock->expects($this->once())
            ->method('removeLinkByUrl')
            ->with('composed_url');

        $result = $this->_block->removeAuthLink('target_block');
        $this->assertSame($this->_block, $result);
    }

    /**
     * @return array
     */
    public static function removeAuthLinkDataProvider()
    {
        return array(
            'Log In url' => array(
                true,
                'getLogoutUrl',
            ),
            'Log Out url' => array(
                false,
                'getLoginUrl',
            ),
        );
    }

    public function testRemoveRegisterLink()
    {
        $this->_helper->expects($this->once())
            ->method('getRegisterUrl')
            ->will($this->returnValue('register_url'));

        $this->_targetBlock->expects($this->once())
            ->method('removeLinkByUrl')
            ->with('register_url');

        $result = $this->_block->removeRegisterLink('target_block');
        $this->assertSame($this->_block, $result);
    }
}
