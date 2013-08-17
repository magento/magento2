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

class Mage_Usa_Model_Shipping_Carrier_UpsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Usa_Model_Shipping_Carrier_Ups
     */
    private $_object;

    public function setUp()
    {
        $simplexmlFactory = $this->getMock('Mage_Usa_Model_Simplexml_ElementFactory', array(), array(), '', false);
        /** @var $simplexmlFactory Mage_Usa_Model_Simplexml_ElementFactory */
        $this->_object = new Mage_Usa_Model_Shipping_Carrier_Ups($simplexmlFactory);
    }

    public function testGetShipAcceptUrl()
    {
        $this->assertEquals($this->_object->getShipAcceptUrl(), 'https://wwwcie.ups.com/ups.app/xml/ShipAccept');
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     */
    public function testGetShipAcceptUrlLive()
    {
        $this->assertEquals($this->_object->getShipAcceptUrl(), 'https://onlinetools.ups.com/ups.app/xml/ShipAccept');
    }

    public function testGetShipConfirmUrl()
    {
        $this->assertEquals($this->_object->getShipConfirmUrl(), 'https://wwwcie.ups.com/ups.app/xml/ShipConfirm');
    }

    /**
     * Test ship accept url for live site
     *
     * @magentoConfigFixture current_store carriers/ups/is_account_live 1
     */
    public function testGetShipConfirmUrlLive()
    {
        $this->assertEquals($this->_object->getShipConfirmUrl(), 'https://onlinetools.ups.com/ups.app/xml/ShipConfirm');
    }
}
