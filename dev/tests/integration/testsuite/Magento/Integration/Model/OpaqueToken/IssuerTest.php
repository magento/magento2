<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Integration\Model\CustomUserContext;
use Magento\Integration\Model\UserToken\UserTokenParameters;
use Magento\Integration\Model\UserToken\UserTokenParametersFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\User\Model\User as UserModel;

class IssuerTest extends TestCase
{
    /**
     * @var Issuer
     */
    private $model;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * @var UserTokenParametersFactory
     */
    private $paramsFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(Issuer::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->userModel = $objectManager->create(UserModel::class);
        $this->paramsFactory = $objectManager->get(UserTokenParametersFactory::class);
    }

    /**
     * Verify that a token can be issued for a customer.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIssueForCustomer(): void
    {
        $customer = $this->customerRepo->get('customer@example.com');
        /** @var UserTokenParameters $params */
        $params = $this->paramsFactory->create();
        $token = $this->model->create(
            new CustomUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER),
            $params
        );

        $this->assertNotEmpty($token);
    }

    /**
     * Verify that a token can be issued for an admin user.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testIssueForAdmin(): void
    {
        $admin = $this->userModel->loadByUsername('adminUser');
        /** @var UserTokenParameters $params */
        $params = $this->paramsFactory->create();
        $token = $this->model->create(
            new CustomUserContext((int) $admin->getId(), UserContextInterface::USER_TYPE_ADMIN),
            $params
        );

        $this->assertNotEmpty($token);
    }
}
