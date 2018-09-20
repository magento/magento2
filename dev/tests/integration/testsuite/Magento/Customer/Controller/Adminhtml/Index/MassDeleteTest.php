<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit_Framework_Constraint;

/**
 * @magentoAppArea adminhtml
 */
class MassDeleteTest extends AbstractBackendController
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Base controller URL
     *
     * @var string
     */
    private $baseControllerUrl = 'http://localhost/index.php/backend/customer/index/index';

    protected function setUp()
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * Validates failure attempts to delete customers from grid.
     *
     * @param array|null $ids
     * @param \PHPUnit_Framework_Constraint $constraint
     * @param string|null $messageType
     * @magentoDataFixture Magento/Customer/_files/five_repository_customers.php
     * @magentoDbIsolation disabled
     * @dataProvider failedRequestDataProvider
     */
    public function testFailedMassDeleteAction($ids, PHPUnit_Framework_Constraint $constraint, $messageType)
    {
        $this->massDeleteAssertions($ids, $constraint, $messageType);
    }

    /**
     * Validates success attempt to delete customer from grid.
     *
     * @param array $emails
     * @param PHPUnit_Framework_Constraint $constraint
     * @param string $messageType
     * @magentoDataFixture Magento/Customer/_files/five_repository_customers.php
     * @magentoDbIsolation disabled
     * @dataProvider successRequestDataProvider
     */
    public function testSuccessMassDeleteAction(array $emails, PHPUnit_Framework_Constraint $constraint, $messageType)
    {
        try {
            $ids = [];
            foreach ($emails as $email) {
                /** @var CustomerInterface $customer */
                $customer = $this->customerRepository->get($email);
                $ids[] = $customer->getId();
            }

            $this->massDeleteAssertions(
                $ids,
                $constraint,
                $messageType
            );
        } catch (LocalizedException $e) {
            self::fail($e->getMessage());
        }
    }

    /**
     * Performs required request and assertions.
     *
     * @param array|null $ids
     * @param PHPUnit_Framework_Constraint $constraint
     * @param string|null $messageType
     */
    private function massDeleteAssertions($ids, PHPUnit_Framework_Constraint $constraint, $messageType)
    {
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get(
            \Magento\Framework\Data\Form\FormKey::class
        );

        $requestData = [
            'selected' => $ids,
            'namespace' => 'customer_listing',
            'form_key' => $formKey->getFormKey()
        ];

        $this->getRequest()
            ->setParams($requestData)
            ->setMethod('POST');
        $this->dispatch('backend/customer/index/massDelete');
        $this->assertSessionMessages(
            $constraint,
            $messageType
        );
        $this->assertRedirect($this->stringStartsWith($this->baseControllerUrl));
    }

    /**
     * Provides sets of data for unsuccessful attempts.
     *
     * @return array
     */
    public function failedRequestDataProvider()
    {
        return [
            [
                'ids' => [],
                'constraint' => self::equalTo(['Please select item(s).']),
                'messageType' => MessageInterface::TYPE_ERROR,
            ],
            [
                'ids' => [111],
                'constraint' => self::isEmpty(),
                'messageType' => null,
            ],
            [
                'ids' => null,
                'constraint' => self::equalTo(['Please select item(s).']),
                'messageType' => MessageInterface::TYPE_ERROR,
            ]
        ];
    }

    /**
     * Provides sets of data for successful attempts.
     *
     * @return array
     */
    public function successRequestDataProvider()
    {
        return [
            [
                'customerEmails' => ['customer1@example.com'],
                'constraint' => self::equalTo(['A total of 1 record(s) were deleted.']),
                'messageType' => MessageInterface::TYPE_SUCCESS,
            ],
            [
                'customerEmails' => ['customer2@example.com', 'customer3@example.com'],
                'constraint' => self::equalTo(['A total of 2 record(s) were deleted.']),
                'messageType' => MessageInterface::TYPE_SUCCESS,
            ],
        ];
    }

    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->getMessages(true);
    }
}
