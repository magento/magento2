<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Unit test CustomerExtractorTest
 */
class CustomerExtractorTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerExtractor */
    protected $customerExtractor;

    /** @var FormFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $formFactory;

    /** @var CustomerInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerFactory;

    /** @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManager;

    /** @var GroupManagementInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerGroupManagement;

    /** @var DataObjectHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $dataObjectHelper;

    /** @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var Form|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerForm;

    /** @var CustomerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerData;

    /** @var StoreInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $store;

    /** @var GroupInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerGroup;

    protected function setUp(): void
    {
        $this->formFactory = $this->getMockForAbstractClass(
            FormFactory::class,
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->customerFactory = $this->getMockForAbstractClass(
            CustomerInterfaceFactory::class,
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->storeManager = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->customerGroupManagement = $this->getMockForAbstractClass(
            GroupManagementInterface::class,
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->request = $this->getMockForAbstractClass(RequestInterface::class, [], '', false);
        $this->customerForm = $this->createMock(Form::class);
        $this->customerData = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false
        );
        $this->store = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            false
        );
        $this->customerGroup = $this->getMockForAbstractClass(
            GroupInterface::class,
            [],
            '',
            false
        );
        $this->customerExtractor = new CustomerExtractor(
            $this->formFactory,
            $this->customerFactory,
            $this->storeManager,
            $this->customerGroupManagement,
            $this->dataObjectHelper
        );
    }

    /**
     * @param int $storeId
     * @param int $websiteId
     * @param array $customerData
     * @dataProvider getDataProvider
     * @return void
     */
    public function testExtract(int $storeId, int $websiteId, array $customerData)
    {
        $this->initializeExpectation($storeId, $websiteId, $customerData);

        $this->assertSame($this->customerData, $this->customerExtractor->extract('form-code', $this->request));
    }

    /**
     * @param int $storeId
     * @param int $websiteId
     * @param array $customerData
     */
    private function initializeExpectation(int $storeId, int $websiteId, array $customerData): void
    {
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('customer', 'form-code')
            ->willReturn($this->customerForm);
        $this->customerForm->expects($this->once())
            ->method('extractData')
            ->with($this->request)
            ->willReturn($customerData);
        $this->customerForm->expects($this->once())
            ->method('compactData')
            ->with($customerData)
            ->willReturn($customerData);
        $this->customerForm->expects($this->once())
            ->method('getAllowedAttributes')
            ->willReturn(['group_id' => 'attribute object']);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customerData);
        $this->dataObjectHelper->expects($this->once())
            ->method('populateWithArray')
            ->with($this->customerData, $customerData, CustomerInterface::class)
            ->willReturn($this->customerData);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->customerData->expects($this->once())
            ->method('setWebsiteId')
            ->with($websiteId);
        $this->customerData->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            'extract data when group id is null' => [
                1,
                1,
                [
                    'firstname' => 'firstname-1',
                    'lastname' => 'firstname-1',
                    'email' => 'email-1.example.com',
                    'group_id' => null
                ]
            ],
            'extract data when group id is not null and default' => [
                1,
                2,
                [
                    'firstname' => 'firstname-2',
                    'lastname' => 'firstname-3',
                    'email' => 'email-2.example.com',
                    'group_id' => 1
                ]
            ],
            'extract data when group id is different from default' => [
                1,
                1,
                [
                    'firstname' => 'firstname-3',
                    'lastname' => 'firstname-3',
                    'email' => 'email-3.example.com',
                    'group_id' => 2
                ]
            ],
        ];
    }
}
