<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for validation customer via backend/customer/index/validate controller.
 *
 * @magentoAppArea adminhtml
 */
class ValidateTest extends AbstractBackendController
{
    /** @var SerializerInterface */
    private $jsonSerializer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jsonSerializer = $this->_objectManager->get(SerializerInterface::class);
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
        $attributeEmptyMessage = 'The "%1" attribute value is empty. Set the attribute and try again.';
        $expectedErrors = [
            'error' => true,
            'messages' => [
                (string)__($attributeEmptyMessage, 'First Name'),
                (string)__($attributeEmptyMessage, 'Last Name'),
                (string)__($attributeEmptyMessage, 'Email'),
            ],
        ];

        $this->dispatchCustomerValidate($postData);
        $errors = $this->jsonSerializer->unserialize($this->getResponse()->getBody());
        $this->assertEquals($expectedErrors, $errors);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @return void
     */
    public function testValidateCustomerWithAddressSuccess(): void
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
        $this->dispatchCustomerValidate($customerData);

        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $errors = $this->jsonSerializer->unserialize($this->getResponse()->getBody());
        $this->assertEquals(['error' => 0], $errors);
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
