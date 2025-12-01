<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Authorization\Model\UserContextInterfaceFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddUserInfoToContextTest extends TestCase
{
    #[
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testExecute()
    {
        $objectManager = Bootstrap::getObjectManager();
        $service = $objectManager->get(AddUserInfoToContext::class);
        $parameters = $objectManager->get(ContextParametersInterface::class);

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $userId = $customer->getId();
        $userType = UserContextInterface::USER_TYPE_CUSTOMER;

        $context = $this->createMock(UserContextInterface::class);
        $context->method('getUserId')->willReturn($userId);
        $context->method('getUserType')->willReturn($userType);

        $service->setUserContext($context);
        $returnedParameters = $service->execute($parameters);

        $this->assertEquals($userId, $returnedParameters->getUserId());
        $this->assertEquals($userType, $returnedParameters->getUserType());

        $extensionAttributes = $returnedParameters->getExtensionAttributesData();
        $this->assertArrayHasKey('is_customer', $extensionAttributes);
        $this->assertTrue($extensionAttributes['is_customer']);

        $session = $objectManager->get(Session::class);

        $this->assertEquals($session->getCustomer()->getData(), $customer->getData());
        $this->assertEquals($session->getCustomerGroupId(), $customer->getGroupId());
    }
}
