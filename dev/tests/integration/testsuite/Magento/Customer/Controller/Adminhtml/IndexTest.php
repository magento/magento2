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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\Backend\Utility\Controller
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $_baseControllerUrl;

    protected function setUp()
    {
        parent::setUp();
        $this->_baseControllerUrl = 'http://localhost/index.php/backend/customer/index/';
    }

    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')
            ->setCustomerData(null);

        /**
         * Unset messages
         */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')
            ->getMessages(true);
    }


    public function testSaveActionWithEmptyPostData()
    {
        $this->getRequest()->setPost(array());
        $this->dispatch('backend/customer/index/save');
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl));
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
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Message\MessageInterface::TYPE_ERROR
        );
        /**
         * Check that customer data were set to session
         */
        $this->assertEquals(
            $post, \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Backend\Model\Session')->getCustomerData()
        );
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new'));
    }

    /**
     * @magentoDbIsolation enabled
     */
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
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Message\MessageInterface::TYPE_ERROR
        );
        /**
         * Check that customer data were set to session
         */
        $this->assertEquals($post, \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Backend\Model\Session')->getCustomerData());
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveActionWithValidCustomerDataAndValidAddressData()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

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
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Message\MessageInterface::TYPE_ERROR);
        /**
         * Check that customer data were set to session
         */
        $this->assertEmpty($objectManager->get('Magento\Backend\Model\Session')->getCustomerData());

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Message\MessageInterface::TYPE_SUCCESS
        );

        /**
         * Check that customer id set and addresses saved
         */
        $registry = $objectManager->get('Magento\Registry');
        $customer = $registry->registry('current_customer');
        $this->assertInstanceOf('Magento\Customer\Model\Customer', $customer);
        $this->assertCount(1, $customer->getAddressesCollection());

        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl
            . 'edit/id/' . $customer->getId() . '/back/1')
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testSaveActionExistingCustomerAndExistingAddressData()
    {
        $post = array(
            'customer_id' => '1',
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
        $this->dispatch('backend/customer/index/save');
        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(array('You saved the customer.')), \Magento\Message\MessageInterface::TYPE_SUCCESS
        );

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /**
         * Check that customer id set and addresses saved
         */
        $customer = $objectManager->get('Magento\Registry')->registry('current_customer');
        $this->assertInstanceOf('Magento\Customer\Model\Customer', $customer);

        /**
         * Addresses should be removed by \Magento\Customer\Model\Resource\Customer::_saveAddresses during _afterSave
         * addressOne - updated
         * addressTwo - removed
         * addressThree - removed
         * _item1 - new address
         */
        $this->assertCount(2, $customer->getAddressesCollection());

        /** @var $savedCustomer \Magento\Customer\Model\Customer */
        $savedCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer');
        $savedCustomer->load($customer->getId());
        /**
         * addressOne - updated
         * _item1 - new address
         */
        $this->assertCount(2, $savedCustomer->getAddressesCollection());

        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'index/key/'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testSaveActionCoreException()
    {
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
        $this->dispatch('backend/customer/index/save');
        /*
        * Check that error message is set
        */
        $this->assertSessionMessages(
            $this->equalTo(array('Customer with the same email already exists in associated website.')),
            \Magento\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertEquals($post, \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Backend\Model\Session')->getCustomerData());
        $this->assertRedirect($this->stringStartsWith($this->_baseControllerUrl . 'new/key/'));
    }
}
