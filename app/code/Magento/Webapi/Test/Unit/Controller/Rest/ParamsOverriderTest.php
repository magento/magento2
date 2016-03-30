<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use \Magento\Authorization\Model\UserContextInterface;

/**
 * Test Magento\Webapi\Controller\Rest\ParamsOverrider
 */
class ParamsOverriderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $requestData Data from the request
     * @param array $parameters Data from config about which parameters to override
     * @param array $expectedOverriddenParams Result of overriding $requestData when applying rules from $parameters
     * @param int $userId The id of the user invoking the request
     * @param int $userType The type of user invoking the request
     *
     * @dataProvider overrideParamsDataProvider
     */
    public function testOverrideParams($requestData, $parameters, $expectedOverriddenParams, $userId, $userType)
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $userContextMock = $this->getMockBuilder('Magento\Authorization\Model\UserContextInterface')
            ->disableOriginalConstructor()->setMethods(['getUserId', 'getUserType'])->getMockForAbstractClass();
        $userContextMock->expects($this->any())->method('getUserId')->will($this->returnValue($userId));
        $userContextMock->expects($this->any())->method('getUserType')->will($this->returnValue($userType));

        $paramOverriderCustomerId = $objectManager->getObject(
            'Magento\Webapi\Controller\Rest\ParamOverriderCustomerId',
            ['userContext' => $userContextMock]
        );

        /** @var \Magento\Webapi\Controller\Rest\ParamsOverrider $paramsOverrider */
        $paramsOverrider = $objectManager->getObject(
            'Magento\Webapi\Controller\Rest\ParamsOverrider',
            ['paramOverriders' => ['%customer_id%' => $paramOverriderCustomerId ]]
        );

        $this->assertEquals($expectedOverriddenParams, $paramsOverrider->override($requestData, $parameters));
    }

    /**
     * @return array
     */
    public function overrideParamsDataProvider()
    {
        return [
            'force false, value present' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => false, 'value' => 'valueOverride']],
                ['Name1' => 'valueIn'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force true, value present' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => true, 'value' => 'valueOverride']],
                ['Name1' => 'valueOverride'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force true, value not present' => [
                ['Name1' => 'valueIn'],
                ['Name2' => ['force' => true, 'value' => 'valueOverride']],
                ['Name1' => 'valueIn', 'Name2' => 'valueOverride'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force false, value not present' => [
                ['Name1' => 'valueIn'],
                ['Name2' => ['force' => false, 'value' => 'valueOverride']],
                ['Name1' => 'valueIn', 'Name2' => 'valueOverride'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force true, value present, override value is %customer_id%' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => true, 'value' => '%customer_id%']],
                ['Name1' => '1234'],
                1234,
                UserContextInterface::USER_TYPE_CUSTOMER,
            ],
            'force true, value present, override value is %customer_id%, not a customer' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => true, 'value' => '%customer_id%']],
                ['Name1' => null],
                1234,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
        ];
    }
}
