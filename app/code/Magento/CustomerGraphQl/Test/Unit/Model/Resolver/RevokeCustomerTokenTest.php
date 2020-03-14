<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerGraphQl\Test\Unit\Model\Resolver;

use Magento\CustomerGraphQl\Model\Resolver\RevokeCustomerToken;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CustomerGraphQl\Model\Resolver\RevokeCustomerToken
 */
class RevokeCustomerTokenTest extends TestCase
{
    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var RevokeCustomerToken
     */
    private $resolver;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var ContextExtensionInterface|MockObject
     */
    private $contextExtensionMock;

    /**
     * @var CustomerTokenServiceInterface|MockObject
     */
    private $customerTokenServiceMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getExtensionAttributes',
                    'getUserId',
                    'getUserType',
                ]
            )
            ->getMock();

        $this->contextExtensionMock = $this->getMockBuilder(ContextExtensionInterface::class)
            ->setMethods(
                [
                    'getIsCustomer',
                    'getStore',
                    'setStore',
                    'setIsCustomer',
                ]
            )
            ->getMock();

        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerTokenServiceMock = $this->getMockBuilder(CustomerTokenServiceInterface::class)
            ->getMockForAbstractClass();

        $this->resolveInfoMock = $this->getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = $this->objectManager->getObject(
            RevokeCustomerToken::class,
            [
                'customerTokenService' => $this->customerTokenServiceMock,
            ]
        );
    }

    /**
     * Test revoke customer token
     */
    public function testRevokeCustomerToken()
    {
        $isCustomer = true;
        $revokeCustomerTokenResult = true;

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getIsCustomer')
            ->willReturn($isCustomer);

        $this->customerTokenServiceMock
            ->expects($this->once())
            ->method('revokeCustomerAccessToken')
            ->willReturn($revokeCustomerTokenResult);

        $this->assertEquals(
            [
                'result' => true
            ],
            $this->resolver->resolve(
                $this->fieldMock,
                $this->contextMock,
                $this->resolveInfoMock
            )
        );
    }

    /**
     * Test mutation when customer isn't authorized.
     *
     * @expectedException \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testCustomerNotAuthorized()
    {
        $isCustomer = false;

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getIsCustomer')
            ->willReturn($isCustomer);

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock
        );
    }
}
