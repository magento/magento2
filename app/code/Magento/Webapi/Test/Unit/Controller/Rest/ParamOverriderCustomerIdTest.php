<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Controller\Rest\ParamOverriderCustomerId;

class ParamOverriderCustomerIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParamOverriderCustomerId
     */
    private $model;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    protected function setUp()
    {
        $this->userContext = $this->getMockBuilder('Magento\Authorization\Model\UserContextInterface')
            ->getMockForAbstractClass();
        $this->model = (new ObjectManager($this))->getObject(
            'Magento\Webapi\Controller\Rest\ParamOverriderCustomerId',
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
            ->will($this->returnValue(UserContextInterface::USER_TYPE_CUSTOMER));
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue($retValue));

        $this->assertSame($retValue, $this->model->getOverriddenValue());
    }

    public function testGetOverriddenValueIsNotCustomer()
    {
        $this->userContext->expects($this->once())
            ->method('getUserType')
            ->will($this->returnValue(UserContextInterface::USER_TYPE_ADMIN));

        $this->assertNull($this->model->getOverriddenValue());
    }
}
