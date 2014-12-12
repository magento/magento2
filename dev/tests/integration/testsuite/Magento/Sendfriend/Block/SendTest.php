<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sendfriend\Block;

use Magento\TestFramework\Helper\Bootstrap;

class SendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sendfriend\Block\Send
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = Bootstrap::getObjectManager()->create('Magento\Sendfriend\Block\Send');
    }

    /**
     * @param string $field
     * @param string $value
     * @dataProvider formDataProvider
     * @covers \Magento\Sendfriend\Block\Send::getUserName
     * @covers \Magento\Sendfriend\Block\Send::getEmail
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
     * @covers \Magento\Sendfriend\Block\Send::getUserName
     * @covers \Magento\Sendfriend\Block\Send::getEmail
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerFieldFromSession($field, $value)
    {
        $logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        /** @var $session \Magento\Customer\Model\Session */
        $session = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session', [$logger]);
        /** @var \Magento\Customer\Api\AccountManagementInterface $service */
        $service = Bootstrap::getObjectManager()->create('Magento\Customer\Api\AccountManagementInterface');
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
