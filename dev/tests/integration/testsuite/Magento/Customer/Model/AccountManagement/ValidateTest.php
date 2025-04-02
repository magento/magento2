<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer validation via customer account management service.
 *
 * @magentoDbIsolation enabled
 */
class ValidateTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var DataObjectHelper */
    private $dataObjectHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->dataObjectHelper = $this->objectManager->get(DataObjectHelper::class);
        parent::setUp();
    }

    /**
     * Validate customer fields.
     *
     * @dataProvider validateFieldsProvider
     *
     * @param array $customerData
     * @param array $expectedResults
     * @return void
     */
    public function testValidateFields(
        array $customerData,
        array $expectedResults
    ): void {
        $customerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerEntity,
            $customerData,
            CustomerInterface::class
        );
        $validationResults = $this->accountManagement->validate($customerEntity);
        $this->assertEquals(
            $expectedResults,
            [
                'valid' => $validationResults->isValid(),
                'messages' => $validationResults->getMessages(),
            ]
        );
    }

    /**
     * @return array
     */
    public static function validateFieldsProvider(): array
    {
        $attributeEmptyMessage = 'The "%1" attribute value is empty. Set the attribute and try again.';
        return [
            'without_required_fields' => [
                'customerData' => [],
                'expectedResults' => [
                    'valid' => false,
                    'messages' => [
                        (string)__($attributeEmptyMessage, 'Associate to Website'),
                        (string)__($attributeEmptyMessage, 'Group'),
                        (string)__($attributeEmptyMessage, 'First Name'),
                        (string)__($attributeEmptyMessage, 'Last Name'),
                        (string)__($attributeEmptyMessage, 'Email'),
                    ],
                ],
            ],
            'with_required_fields' => [
                'customerData' => [
                    CustomerInterface::WEBSITE_ID => 1,
                    CustomerInterface::GROUP_ID => 1,
                    CustomerInterface::FIRSTNAME => 'Jane',
                    CustomerInterface::LASTNAME => 'Doe',
                    CustomerInterface::EMAIL => 'janedoe' . uniqid() . '@example.com',
                ],
                'expectedResults' => [
                    'valid' => true,
                    'messages' => [],
                ],
            ],
        ];
    }
}
