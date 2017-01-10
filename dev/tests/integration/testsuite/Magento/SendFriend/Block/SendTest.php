<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Block;

use Magento\TestFramework\Helper\Bootstrap;

class SendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SendFriend\Block\Send
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = Bootstrap::getObjectManager()->create(\Magento\SendFriend\Block\Send::class);
    }

    /**
     * @param string $field
     * @param string $value
     * @dataProvider formDataProvider
     * @covers \Magento\SendFriend\Block\Send::getUserName
     * @covers \Magento\SendFriend\Block\Send::getEmail
     */
    public function testGetCustomerFieldFromFormData($field, $value)
    {
        $formData = ['sender' => [$field => $value]];
        $this->_block->setFormData($formData);
        $this->assertEquals(trim($value), $this->_callBlockMethod($field));
    }

    /**
     * @return array
     */
    public function formDataProvider()
    {
        return [
            ['name', 'Customer Form Name'],
            ['email', 'customer_form_email@example.com']
        ];
    }

    /**
     * @param string $field
     * @param string $value
     * @dataProvider customerSessionDataProvider
     * @covers \Magento\SendFriend\Block\Send::getUserName
     * @covers \Magento\SendFriend\Block\Send::getEmail
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerFieldFromSession($field, $value)
    {
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class, [], [], '', false);
        /** @var $session \Magento\Customer\Model\Session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Session::class, [$logger]);
        /** @var \Magento\Customer\Api\AccountManagementInterface $service */
        $service = Bootstrap::getObjectManager()->create(\Magento\Customer\Api\AccountManagementInterface::class);
        $customer = $service->authenticate('customer@example.com', 'password');
        $session->setCustomerDataAsLoggedIn($customer);
        $this->assertEquals($value, $this->_callBlockMethod($field));
    }

    /**
     * @return array
     */
    public function customerSessionDataProvider()
    {
        return [
            ['name', 'John Smith'],
            ['email', 'customer@example.com']
        ];
    }

    /**
     * Call block method based on form field
     *
     * @param string $field
     * @return null|string
     */
    protected function _callBlockMethod($field)
    {
        switch ($field) {
            case 'name':
                return $this->_block->getUserName();
            case 'email':
                return $this->_block->getEmail();
            default:
                return null;
        }
    }
}
