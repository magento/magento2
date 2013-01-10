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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_CustomerControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $_baseControllerUrl;

    public function setUp()
    {
        parent::setUp();
        $this->_baseControllerUrl = 'http://localhost/index.php/backend/admin/customer/';
    }

    public function tearDown()
    {
        /**
         * Unset customer data
         */
        Mage::getSingleton('Mage_Backend_Model_Session')->setCustomerData(null);

        /**
         * Unset messages
         */
        Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(true);
    }


    public function testSaveActionWithEmptyPostData()
    {
        $this->getRequest()->setPost(array());
        $this->dispatch('backend/admin/customer/save');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key'));
    }

    public function testSaveActionWithInvalidFormData()
    {
        $post = array(
            'account' => array(
                'middlename' => 'test middlename',
                'group_id' => 1
            )
        );
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/admin/customer/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertNotEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());
        /**
         * Check that customer data were set to session
         */
        $this->assertEquals($post, Mage::getSingleton('Mage_Backend_Model_Session')->getCustomerData());
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key'));
    }

    public function testSaveActionWithInvalidCustomerAddressData()
    {
        $post = array(
            'account' => array(
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 0,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'exmaple@domain.com',
                'default_billing' => '_item1',
            ),
            'address' => array('_item1' => array()),
        );
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/admin/customer/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertNotEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());
        /**
         * Check that customer data were set to session
         */
        $this->assertEquals($post, Mage::getSingleton('Mage_Backend_Model_Session')->getCustomerData());
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithValidCustomerDataAndValidAddressData()
    {
        $post = array(
            'account' => array(
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 0,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'exmaple@domain.com',
                'default_billing' => '_item1',
                'password' => 'auto'
            ),
            'address' => array('_item1' => array(
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'street' => array(
                    'test street'
                ),
                'city' => 'test city',
                'country_id' => 'US',
                'postcode' => '01001',
                'telephone' => '+7000000001',
            )),
        );
        $this->getRequest()->setPost($post);
        $this->getRequest()->setParam('back', '1');
        $this->dispatch('backend/admin/customer/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());
        /**
         * Check that customer data were set to session
         */
        $this->assertEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getCustomerData());

        /**
         * Check that success message is set
         */
        $this->assertCount(1,
            Mage::getSingleton('Mage_Backend_Model_Session')
                ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS),
            'Success message was not set'
        );

        /**
         * Check that customer id set and addresses saved
         */
        $customer = Mage::registry('current_customer');
        $this->assertInstanceOf('Mage_Customer_Model_Customer', $customer);
        $this->assertCount(1, $customer->getAddressesCollection());

        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl
            . 'edit/id/' . $customer->getId() . '/back/1/key/')
        );
    }

    public function testSaveActionExistingCustomerAndExistingAddressData()
    {
        $this->markTestIncomplete('Bug MAGETWO-2986');
        $post = array(
            'account' => array(
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'exmaple@domain.com',
                'default_shipping' => '_item1',
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',

            ),
            'address' => array(
                '1' => array(
                    'firstname' => 'update firstname',
                    'lastname' => 'update lastname',
                    'street' => array('update street'),
                    'city' => 'update city',
                    'country_id' => 'US',
                    'postcode' => '01001',
                    'telephone' => '+7000000001',
                ),
                '_item1' => array(
                    'firstname' => 'test firstname',
                    'lastname' => 'test lastname',
                    'street' => array('test street'),
                    'city' => 'test city',
                    'country_id' => 'US',
                    'postcode' => '01001',
                    'telephone' => '+7000000001',
                ),
                '_template_' => array(
                    'firstname' => '',
                    'lastname' => '',
                    'street' => array(),
                    'city' => '',
                    'country_id' => 'US',
                    'postcode' => '',
                    'telephone' => '',
                )
            ),
        );
        $this->getRequest()->setPost($post);
        $this->getRequest()->setParam('customer_id', 1);
        $this->dispatch('backend/admin/customer/save');
        /**
         * Check that success message is set
         */
        $successMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertEquals('The customer has been saved.', current($successMessages)->getCode());

        /**
         * Check that customer id set and addresses saved
         */
        $customer = Mage::registry('current_customer');
        $this->assertInstanceOf('Mage_Customer_Model_Customer', $customer);

        /**
         * addressOne - updated
         * addressTwo - removed
         * addressThree - removed
         * _item1 - new address
         */
        $this->assertCount(4, $customer->getAddressesCollection());

        /** @var $savedCustomer Mage_Customer_Model_Customer */
        $savedCustomer = Mage::getModel('Mage_Customer_Model_Customer');
        $savedCustomer->load($customer->getId());
        /**
         * addressOne - updated
         * _item1 - new address
         */
        $this->assertCount(2, $savedCustomer->getAddressesCollection());

        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
    }

    public function testSaveActionCoreException()
    {
        $this->markTestIncomplete('Bug MAGETWO-2986');
        $post = array(
            'account' => array(
                'middlename' => 'test middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'test firstname',
                'lastname' => 'test lastname',
                'email' => 'exmaple@domain.com',
                'password' => 'auto',
            ),
        );
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/admin/customer/save');
        /*
        * Check that error message is set
        */
        $errorMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getErrors();
        $this->assertEquals('This customer email already exists', current($errorMessages)->getCode());
        $this->assertEquals($post, Mage::getSingleton('Mage_Backend_Model_Session')->getCustomerData());
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'edit/key/'));
    }
}
