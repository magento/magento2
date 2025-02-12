<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\JwtUserToken\Api\Data\Revoked;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;
use Magento\JwtUserToken\Model\Data\JwtUserContext;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User as UserModel;
use PHPUnit\Framework\TestCase;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Api\Data\UserTokenParametersInterfaceFactory;

class RevokerTest extends TestCase
{
    /**
     * @var Revoker;
     */
    private $model;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Issuer
     */
    private $issuer;

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
     * @var RevokedValidator
     */
    private $revokedValidator;

    /**
     * @var RevokedRepositoryInterface
     */
    private $revokedRepo;

    /**
     * @var int|null
     */
    private $clearForId;

    /**
     * @var int|null
     */
    private $clearForType;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();

        $this->model = $objectManager->get(Revoker::class);
        $this->reader = $objectManager->get(Reader::class);
        $this->issuer = $objectManager->get(Issuer::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->userModel = $objectManager->create(UserModel::class);
        $this->paramsFactory = $objectManager->get(UserTokenParametersInterfaceFactory::class);
        $this->revokedValidator = $objectManager->get(RevokedValidator::class);
        $this->revokedRepo = $objectManager->get(RevokedRepositoryInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->clearForId) {
            $this->revokedRepo->saveRevoked(new Revoked($this->clearForType, $this->clearForId, time() - 60));
            $this->clearForType = null;
            $this->clearForId = null;
        }
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
        $token = $this->issuer->create(
            new JwtUserContext((int) $customer->getId(), UserContextInterface::USER_TYPE_CUSTOMER),
            $params
        );

        $this->model->revokeFor(
            new JwtUserContext(
                $this->clearForId = (int) $customer->getId(),
                $this->clearForType = UserContextInterface::USER_TYPE_CUSTOMER
            )
        );

        $this->expectException(AuthorizationException::class);
        $this->revokedValidator->validate($this->reader->read($token));
    }
}
