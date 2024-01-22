<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Jwt\Claim\PrivateClaim;
use Magento\JwtUserToken\Model\Data\JwtTokenParameters;
use Magento\JwtUserToken\Model\Data\JwtUserContext;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User as UserModel;
use PHPUnit\Framework\TestCase;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Api\Data\UserTokenParametersInterfaceFactory;

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
     * @var UserTokenParametersInterfaceFactory
     */
    private $paramsFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(Issuer::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->userModel = $objectManager->create(UserModel::class);
        $this->paramsFactory = $objectManager->get(UserTokenParametersInterfaceFactory::class);
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
        /** @var UserTokenParametersInterface $params */
        $params = $this->paramsFactory->create();
        $jwtParams = new JwtTokenParameters();
        $jwtParams->setClaims([new PrivateClaim('custom-claim', 'value')]);
        $params->getExtensionAttributes()->setJwtParams($jwtParams);
        $token = $this->model->create(
            new JwtUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER),
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
        /** @var UserTokenParametersInterface $params */
        $params = $this->paramsFactory->create();
        $token = $this->model->create(
            new JwtUserContext((int) $admin->getId(), UserContextInterface::USER_TYPE_ADMIN),
            $params
        );

        $this->assertNotEmpty($token);
    }
}
