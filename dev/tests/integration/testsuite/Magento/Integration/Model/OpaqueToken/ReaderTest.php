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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User as UserModel;
use PHPUnit\Framework\TestCase;
use Magento\Integration\Model\UserToken\UserTokenParametersFactory;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
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

    /**
     * @var Issuer
     */
    private $issuer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(Reader::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->userModel = $objectManager->create(UserModel::class);
        $this->paramsFactory = $objectManager->get(UserTokenParametersFactory::class);
        $this->issuer = $objectManager->get(Issuer::class);
    }

    /**
     * Verify that a token can be accepted for a customer.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testReadingCustomer(): void
    {
        //Preparing the token
        $customer = $this->customerRepo->get('customer@example.com');
        /** @var UserTokenParameters $params */
        $params = $this->paramsFactory->create(
            ['issued' => $issued = (new \DateTimeImmutable())->sub(new \DateInterval('PT1H'))]
        );
        $token = $this->issuer->create(
            new CustomUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER),
            $params
        );

        $data = $this->model->read($token);
        $this->assertEquals(UserContextInterface::USER_TYPE_CUSTOMER, $data->getUserContext()->getUserType());
        $this->assertEquals((int) $customer->getId(), $data->getUserContext()->getUserId());
        $this->assertEquals($issued->format('Y-m-d H:i:s'), $data->getData()->getIssued()->format('Y-m-d H:i:s'));
        $this->assertGreaterThan($issued, $data->getData()->getExpires());
    }

    /**
     * Verify that a token can be accepted for an admin user.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/User/_files/user_with_role.php
     */
    public function testRadingAdmin(): void
    {
        //Preparing the token
        $admin = $this->userModel->loadByUsername('adminUser');
        /** @var UserTokenParameters $params */
        $params = $this->paramsFactory->create();
        $token = $this->issuer->create(
            new CustomUserContext((int) $admin->getId(), UserContextInterface::USER_TYPE_ADMIN),
            $params
        );

        $data = $this->model->read($token);
        $this->assertEquals(UserContextInterface::USER_TYPE_ADMIN, $data->getUserContext()->getUserType());
        $this->assertEquals((int) $admin->getId(), $data->getUserContext()->getUserId());
        $this->assertGreaterThan($data->getData()->getIssued(), $data->getData()->getExpires());
    }
}
