<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Controller\Rest\ParamOverriderCustomerId;
use PHPUnit\Framework\TestCase;

/**
 * Class ParamOverriderCustomerIdTest
 */
class ParamOverriderCustomerIdTest extends TestCase
{
    /**
     * @var ParamOverriderCustomerId
     */
    private $model;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->userContext = $this->getMockBuilder(UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->model = (new ObjectManager($this))->getObject(
            ParamOverriderCustomerId::class,
            [
                'userContext' => $this->userContext
            ]
        );
    }

    /**
     * Test getOverriddenValue method.
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetOverriddenValueIsCustomer(): void
    {
        $retValue = 'retValue';

        $this->userContext
            ->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext
            ->expects($this->once())
            ->method('getUserId')
            ->willReturn($retValue);

        $this->assertSame($retValue, $this->model->getOverriddenValue());
    }

    /**
     * Test getOverriddenValue method.
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetOverriddenValueIsNotCustomer(): void
    {
        $this->userContext
            ->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->assertNull($this->model->getOverriddenValue());
    }
}
