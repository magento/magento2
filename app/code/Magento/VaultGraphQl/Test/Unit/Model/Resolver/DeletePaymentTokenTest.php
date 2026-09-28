<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\VaultGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\VaultGraphQl\Model\Resolver\DeletePaymentToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Test class for \Magento\VaultGraphQl\Model\Resolver\DeletePaymentToken
 */
class DeletePaymentTokenTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Object Manager Instance
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Testable Object
     *
     * @var DeletePaymentToken
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
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var PaymentTokenManagementInterface|MockObject
     */
    private $paymentTokenManagementMock;

    /**
     * @var PaymentTokenRepositoryInterface|MockObject
     */
    private $paymentTokenRepositoryMock;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    private $paymentTokenMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->createMock(ContextInterface::class);

        $this->contextExtensionMock = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            [
                'getIsCustomer',
                'getStore',
                'setStore',
                'setIsCustomer',
            ]
        );

        $this->fieldMock = $this->createMock(Field::class);

        $this->paymentTokenManagementMock = $this->createMock(PaymentTokenManagementInterface::class);

        $this->paymentTokenRepositoryMock = $this->createMock(PaymentTokenRepositoryInterface::class);

        $this->paymentTokenMock = $this->createMock(PaymentTokenInterface::class);

        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->resolver = $this->objectManager->getObject(
            DeletePaymentToken::class,
            [
                'paymentTokenManagement' => $this->paymentTokenManagementMock,
                'paymentTokenRepository' => $this->paymentTokenRepositoryMock,
            ]
        );
    }

    /**
     * Test delete customer payment token
     */
    public function testDeleteCustomerPaymentToken()
    {
        $isCustomer = true;
        $paymentTokenResult = true;

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getIsCustomer')
            ->willReturn($isCustomer);

        $this->paymentTokenManagementMock
            ->expects($this->once())
            ->method('getByPublicHash')
            ->willReturn($this->paymentTokenMock);

        $this->paymentTokenRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->paymentTokenMock)
            ->willReturn($paymentTokenResult);

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
     */
    public function testCustomerNotAuthorized()
    {
        $this->expectException('Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException');
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
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

    /**
     * Test mutation when provided token ID does not exist
     */
    public function testCustomerPaymentTokenNotExists()
    {
        $isCustomer = true;
        $token = false;

        $this->contextMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->contextExtensionMock);

        $this->contextExtensionMock
            ->expects($this->once())
            ->method('getIsCustomer')
            ->willReturn($isCustomer);

        $this->paymentTokenManagementMock
            ->expects($this->once())
            ->method('getByPublicHash')
            ->willReturn($token);

        $this->expectException(GraphQlNoSuchEntityException::class);

        $this->resolver->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock
        );
    }
}
