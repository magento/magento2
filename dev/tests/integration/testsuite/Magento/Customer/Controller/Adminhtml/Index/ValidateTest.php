<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for validation customer via backend/customer/index/validate controller.
 *
 * @magentoAppArea adminhtml
 */
class ValidateTest extends AbstractBackendController
{
    /** @var Json */
    protected $jsonSerializer;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jsonSerializer = $this->_objectManager->get(Json::class);
    }

    /**
     * Validate customer with exception
     *
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testValidateCustomerErrors(): void
    {
        $postData = [
            'customer' => [],
        ];
        $expectedErrors = [
            'error' => true,
            'messages' => [
                0 => 'The "First Name" attribute value is empty. Set the attribute and try again.',
                1 => 'The "Last Name" attribute value is empty. Set the attribute and try again.',
                2 => 'The "Email" attribute value is empty. Set the attribute and try again.',
            ],
        ];

        $this->dispatchCustomerValidate($postData);
        $errors = $this->jsonSerializer->unserialize($this->getResponse()->getBody());
        $this->assertEquals($errors, $expectedErrors);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testValidateCustomerWithAddressSuccess()
    {
        $customerData = [
            'customer' => [
                'entity_id' => '1',
                'middlename' => 'new middlename',
                'group_id' => 1,
                'website_id' => 1,
                'firstname' => 'new firstname',
                'lastname' => 'new lastname',
                'email' => 'example@domain.com',
                'default_shipping' => '_item1',
                'new_password' => 'auto',
                'sendemail_store_id' => '1',
                'sendemail' => '1',
            ],
            'address' => [
                '_item1' => [
                    'firstname' => 'update firstname',
                    'lastname' => 'update lastname',
                    'street' => ['update street'],
                    'city' => 'update city',
                    'country_id' => 'US',
                    'region_id' => 10,
                    'postcode' => '01001',
                    'telephone' => '+7000000001',
                ],
                '_template_' => [
                    'firstname' => '',
                    'lastname' => '',
                    'street' => [],
                    'city' => '',
                    'country_id' => 'US',
                    'postcode' => '',
                    'telephone' => '',
                ],
            ],
        ];
        /**
         * set customer data
         */
        $this->dispatchCustomerValidate($customerData);
        $body = $this->getResponse()->getBody();

        /**
         * Check that no errors were generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $this->assertEquals('{"error":0}', $body);
    }

    /**
     * Validate customer using backend/customer/index/validate action.
     *
     * @param array $postData
     * @return void
     */
    private function dispatchCustomerValidate(array $postData): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/customer/index/validate');
    }
}
