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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Customer_Block_Account_LinkTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Customer_Block_Account_Link
     */
    protected $_block;

    /**
     * @var Mage_Page_Block_Template_Links
     */
    protected $_links;

    public function setUp()
    {
        $this->_block = new Mage_Customer_Block_Account_Link();
        $layout = new Mage_Core_Model_Layout;
        $this->_block->setLayout($layout);
        $layout->addBlock('Mage_Page_Block_Template_Links', 'links');
        $this->_links = $layout->getBlock('links');
    }

    public function testAddAccountLink()
    {
        $this->assertEmpty($this->_links->getLinks());
        $this->_block->addAccountLink('links', 1);

        $links = $this->_links->getLinks();
        $this->assertNotEmpty($links);
        $this->assertEquals('My Account', $links[1]->getLabel());
    }

    public function testAddRegisterLink()
    {
        $this->assertEmpty($this->_links->getLinks());
        $this->_block->addRegisterLink('links', 1);
        $links = $this->_links->getLinks();
        $this->assertEquals('register', $links[1]->getLabel());
    }

    public function testAddAuthLinkLogIn()
    {
        $this->assertEmpty($this->_links->getLinks());
        $this->_block->addAuthLink('links', 1);

        $links = $this->_links->getLinks();
        $this->assertEquals('Log In', $links[1]->getLabel());

    }

    /**
     * @magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testAddAuthLinkLogOut()
    {
        Mage::getSingleton('Mage_Customer_Model_Session')->login('customer@example.com', 'password');
        $this->_block->addAuthLink('links', 1);
        $links = $this->_links->getLinks();
        $this->assertEquals('Log Out', $links[1]->getLabel());
    }
}
