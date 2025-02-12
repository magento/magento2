<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\OpaqueToken;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Model\CustomUserContext;
use Magento\Integration\Model\UserToken\UserTokenParameters;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User as UserModel;
use PHPUnit\Framework\TestCase;
use Magento\Integration\Model\UserToken\UserTokenParametersFactory;

class RevokerTest extends TestCase
{
    /**
     * @var Revoker
     */
    private $model;

    /**
     * @var Reader
     */
    private $reader;

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

        $this->model = $objectManager->get(Revoker::class);
        $this->reader = $objectManager->get(Reader::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->userModel = $objectManager->create(UserModel::class);
        $this->paramsFactory = $objectManager->get(UserTokenParametersFactory::class);
        $this->issuer = $objectManager->get(Issuer::class);
    }

    /**
     * Verify that tokens are actually being revoked.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRevoking(): void
    {
        //Preparing the token
        $customer = $this->customerRepo->get('customer@example.com');
        /** @var UserTokenParameters $params */
        $params = $this->paramsFactory->create();
        $token = $this->issuer->create(
            new CustomUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER),
            $params
        );

        $this->model->revokeFor(
            new CustomUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER)
        );
        $this->expectException(UserTokenException::class);
        $this->reader->read($token);
    }
}
