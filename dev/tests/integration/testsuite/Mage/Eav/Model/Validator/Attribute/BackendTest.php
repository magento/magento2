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

/**
 * Test for Mage_Eav_Model_Validator_Attribute_Backend
 */
class Mage_Eav_Model_Validator_Attribute_BackendTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Eav_Model_Validator_Attribute_Backend
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Eav_Model_Validator_Attribute_Backend();
    }

    /**
     * Test method for Mage_Eav_Model_Validator_Attribute_Backend::isValid
     *
     * @magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testIsValid()
    {
        /** @var $entity Mage_Customer_Model_Customer */
        $entity = Mage::getModel('Mage_Customer_Model_Customer')->load(1);

        $this->assertTrue($this->_model->isValid($entity));
        $this->assertEmpty($this->_model->getMessages());

        $entity->setData('email', null);
        $this->assertFalse($this->_model->isValid($entity));
        $this->assertArrayHasKey('email', $this->_model->getMessages());

        $entity->setData('store_id', null);
        $this->assertFalse($this->_model->isValid($entity));
        $this->assertArrayHasKey('email', $this->_model->getMessages());
        $this->assertArrayHasKey('store_id', $this->_model->getMessages());
    }
}
