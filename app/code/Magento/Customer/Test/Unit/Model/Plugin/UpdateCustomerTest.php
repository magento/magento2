<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\Plugin\UpdateCustomer;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for UpdateCustomer plugin.
 */
class UpdateCustomerTest extends TestCase
{
    private const CUSTOMER_ID = 1;

    /**
     * @var UpdateCustomer
     */
    private UpdateCustomer $updateCustomerPlugin;

    /**
     * @var RestRequest|Stub
     */
    private $request;

    /**
     * @var UserContextInterface|Stub
     */
    private $userContext;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createStub(RestRequest::class);
        $this->userContext = $this->createStub(UserContextInterface::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);

        $this->updateCustomerPlugin = new UpdateCustomer($this->request, $this->userContext);
    }

    /**
     * A partial update by a privileged user (admin or integration) must preserve any custom
     * attribute not referenced in the request and apply the new value for the one that was sent.
     */
    #[DataProvider('privilegedUserTypeDataProvider')]
    public function testBeforeSavePreservesUnrelatedCustomAttributeForPrivilegedUser(int $userType): void
    {
        $originCustomer = $this->createCustomerData([
            CustomerInterface::CUSTOM_ATTRIBUTES => [
                'cus_attr1' => $this->createAttribute('cus_attr1', '1'),
                'cust_attr2' => $this->createAttribute('cust_attr2', '1'),
            ],
        ]);
        $incomingCustomer = $this->createCustomerData([
            CustomerInterface::CUSTOM_ATTRIBUTES => [
                'cust_attr2' => $this->createAttribute('cust_attr2', '0'),
            ],
        ]);

        $this->userContext->method('getUserType')->willReturn($userType);
        $this->userContext->method('getUserId')->willReturn(0);
        $this->request->method('getParam')->willReturn((string)self::CUSTOMER_ID);
        $this->request->method('getBodyParams')->willReturn([]);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($originCustomer);

        [$result, $passwordHash] = $this->updateCustomerPlugin->beforeSave(
            $this->customerRepository,
            $incomingCustomer,
            'password-hash'
        );

        $this->assertSame('1', $result->getCustomAttribute('cus_attr1')->getValue());
        $this->assertSame('0', $result->getCustomAttribute('cust_attr2')->getValue());
        $this->assertSame('password-hash', $passwordHash);
    }

    public static function privilegedUserTypeDataProvider(): array
    {
        return [
            'admin user' => [UserContextInterface::USER_TYPE_ADMIN],
            'integration user' => [UserContextInterface::USER_TYPE_INTEGRATION],
        ];
    }

    /**
     * A customer updating their own account (self-service partial update) must get the same
     * preservation behaviour as a privileged user.
     */
    public function testBeforeSavePreservesUnrelatedCustomAttributeForCustomerUpdatingOwnAccount(): void
    {
        $originCustomer = $this->createCustomerData([
            CustomerInterface::CUSTOM_ATTRIBUTES => [
                'cus_attr1' => $this->createAttribute('cus_attr1', '1'),
                'cust_attr2' => $this->createAttribute('cust_attr2', '1'),
            ],
        ]);
        $incomingCustomer = $this->createCustomerData([
            CustomerInterface::CUSTOM_ATTRIBUTES => [
                'cust_attr2' => $this->createAttribute('cust_attr2', '0'),
            ],
        ]);

        $this->userContext->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->method('getUserId')->willReturn(self::CUSTOMER_ID);
        $this->request->method('getParam')->willReturn((string)self::CUSTOMER_ID);
        $this->request->method('getBodyParams')->willReturn([]);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($originCustomer);

        [$result] = $this->updateCustomerPlugin->beforeSave($this->customerRepository, $incomingCustomer, null);

        $this->assertSame('1', $result->getCustomAttribute('cus_attr1')->getValue());
        $this->assertSame('0', $result->getCustomAttribute('cust_attr2')->getValue());
    }

    /**
     * When the current customer session does not match the requested customer id, the plugin
     * must not merge against another customer's data and must not load it from the repository.
     */
    public function testBeforeSaveSkipsMergeWhenCustomerSessionDoesNotMatchRequestedId(): void
    {
        $incomingCustomer = $this->createCustomerData([
            CustomerInterface::CUSTOM_ATTRIBUTES => [
                'cust_attr2' => $this->createAttribute('cust_attr2', '0'),
            ],
        ]);

        $this->userContext->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->method('getUserId')->willReturn(99);
        $this->request->method('getParam')->willReturn((string)self::CUSTOMER_ID);
        $this->request->method('getBodyParams')->willReturn([]);
        $this->customerRepository->expects($this->never())->method('getById');

        [$result] = $this->updateCustomerPlugin->beforeSave($this->customerRepository, $incomingCustomer, null);

        $this->assertSame($incomingCustomer, $result);
    }

    /**
     * Build a real Customer data object (not a mock) so that the plugin's use of native
     * DataObject behaviour (setData/getData/__toArray/clone) is exercised faithfully.
     */
    private function createCustomerData(array $data): CustomerData
    {
        return new CustomerData(
            $this->createStub(ExtensionAttributesFactory::class),
            $this->createStub(AttributeValueFactory::class),
            $this->createStub(CustomerMetadataInterface::class),
            $data
        );
    }

    private function createAttribute(string $code, string $value): AttributeValue
    {
        return new AttributeValue(['attribute_code' => $code, 'value' => $value]);
    }
}
