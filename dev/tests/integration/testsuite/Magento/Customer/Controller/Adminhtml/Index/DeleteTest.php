<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for delete customer via backend/customer/index/delete controller.
 *
 * @magentoAppArea adminhtml
 */
class DeleteTest extends AbstractBackendController
{
    /** @var FormKey */
    private $formKey;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->formKey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * Delete customer
     *
     * @dataProvider deleteCustomerProvider
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     *
     * @param array $paramsData
     * @param string $expected
     * @return void
     */
    public function testDeleteCustomer(array $paramsData, array $expected): void
    {
        $this->dispatchCustomerDelete($paramsData);

        $this->assertRedirect($this->stringContains('customer/index'));
        $this->assertSessionMessages(
            $this->equalTo([(string)__(...$expected['message'])]),
            $expected['message_type']
        );
    }

    /**
     * Delete customer provider
     *
     * @return array
     */
    public static function deleteCustomerProvider(): array
    {
        return [
            'delete_customer_success' => [
                'paramsData' => [
                    'id' => 1,
                ],
                'expected' => [
                    'message' => ['You deleted the customer.'],
                    'message_type' => MessageInterface::TYPE_SUCCESS,
                ],
            ],
            'not_existing_customer_error' => [
                'paramsData' => [
                    'id' => 2,
                ],
                'expected' => [
                    'message' => [
                        'No such entity with %fieldName = %fieldValue',
                        [
                            'fieldName' => 'customerId',
                            'fieldValue' => '2',
                        ],
                    ],
                    'message_type' => MessageInterface::TYPE_ERROR,
                ],
            ],
        ];
    }

    /**
     * Delete customer using backend/customer/index/delete action.
     *
     * @param array $params
     * @return void
     */
    private function dispatchCustomerDelete(array $params): void
    {
        $params['form_key'] = $this->formKey->getFormKey();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams($params);
        $this->dispatch('backend/customer/index/delete');
    }
}
