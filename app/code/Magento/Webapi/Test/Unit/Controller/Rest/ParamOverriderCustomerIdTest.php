<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Controller\Rest\ParamOverriderCustomerId;

class ParamOverriderCustomerIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ParamOverriderCustomerId
     */
    private $model;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    protected function setUp(): void
    {
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->getMockForAbstractClass();
        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Webapi\Controller\Rest\ParamOverriderCustomerId::class,
            [
                'userContext' => $this->userContext
            ]
        );
    }
    
    public function testGetOverriddenValueIsCustomer()
    {
        $retValue = 'retValue';

        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($retValue);

        $this->assertSame($retValue, $this->model->getOverriddenValue());
    }

    public function testGetOverriddenValueIsNotCustomer()
    {
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->assertNull($this->model->getOverriddenValue());
    }
}
