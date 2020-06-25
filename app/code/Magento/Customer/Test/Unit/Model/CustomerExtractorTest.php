<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test CustomerExtractorTest
 */
class CustomerExtractorTest extends TestCase
{
    /** @var CustomerExtractor */
    protected $customerExtractor;

    /** @var FormFactory|MockObject */
    protected $formFactory;

    /** @var CustomerInterfaceFactory|MockObject */
    protected $customerFactory;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var GroupManagementInterface|MockObject */
    protected $customerGroupManagement;

    /** @var DataObjectHelper|MockObject */
    protected $dataObjectHelper;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var Form|MockObject */
    protected $customerForm;

    /** @var CustomerInterface|MockObject */
    protected $customerData;

    /** @var StoreInterface|MockObject */
    protected $store;

    /** @var GroupInterface|MockObject */
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

    public function testExtract()
    {
        $customerData = [
            'firstname' => 'firstname',
            'lastname' => 'firstname',
            'email' => 'email.example.com',
        ];

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
            ->willReturn(1);
        $this->store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->customerData->expects($this->once())
            ->method('setWebsiteId')
            ->with(1);
        $this->customerData->expects($this->once())
            ->method('setStoreId')
            ->with(1);

        $this->assertSame($this->customerData, $this->customerExtractor->extract('form-code', $this->request));
    }
}
