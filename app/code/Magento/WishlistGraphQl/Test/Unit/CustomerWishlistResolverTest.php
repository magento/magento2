<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Test\Unit;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Model\Resolver\CustomerWishlistResolver;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerWishlistResolverTest extends TestCase
{
    use MockCreationTrait;

    private const STUB_CUSTOMER_ID = 1;

    /**
     * @var MockObject|ContextInterface
     */
    private $contextMock;

    /**
     * @var MockObject|ContextExtensionInterface
     */
    private $extensionAttributesMock;

    /**
     * @var MockObject|WishlistFactory
     */
    private $wishlistFactoryMock;

    /**
     * @var MockObject|Wishlist
     */
    private $wishlistMock;

    /**
     * @var CustomerWishlistResolver
     */
    private $resolver;

    /**
     * @var Config|MockObject
     */
    private $wishlistConfigMock;

    /**
     * Build the Testing Environment
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMockWithReflection(
            ContextInterface::class,
            ['getExtensionAttributes', 'getUserId']
        );

        $this->extensionAttributesMock = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getStore', 'setStore', 'getIsCustomer', 'setIsCustomer']
        );

        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);

        $this->wishlistFactoryMock = $this->createPartialMock(WishlistFactory::class, ['create']);

        $this->wishlistMock = $this->createPartialMockWithReflection(
            Wishlist::class,
            ['getSharingCode', 'getUpdatedAt', 'loadByCustomerId', 'getId', 'getItemsCount']
        );

        $this->wishlistConfigMock = $this->createMock(Config::class);

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(CustomerWishlistResolver::class, [
            'wishlistFactory' => $this->wishlistFactoryMock,
            'wishlistConfig' => $this->wishlistConfigMock
        ]);
    }

    /**
     * Verify if Authorization exception is being thrown when User not logged in
     */
    public function testThrowExceptionWhenUserNotAuthorized(): void
    {
        $this->wishlistConfigMock->method('isEnabled')->willReturn(true);

        // Given
        $this->extensionAttributesMock->method('getIsCustomer')
            ->willReturn(false);

        // Then
        $this->expectException(GraphQlAuthorizationException::class);
        $this->wishlistFactoryMock->expects($this->never())
            ->method('create');

        // When
        $this->resolver->resolve(
            $this->getFieldStub(),
            $this->contextMock,
            $this->getResolveInfoStub()
        );
    }

    /**
     * Verify if Wishlist instance is created for currently Authorized user
     */
    public function testFactoryCreatesWishlistByAuthorizedCustomerId(): void
    {
        $this->wishlistConfigMock->method('isEnabled')->willReturn(true);

        // Given
        $this->extensionAttributesMock->method('getIsCustomer')
            ->willReturn(true);

        $this->contextMock->method('getUserId')
            ->willReturn(self::STUB_CUSTOMER_ID);

        // Then
        $this->wishlistMock->expects($this->once())
            ->method('loadByCustomerId')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturnSelf();

        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->wishlistMock);

        // When
        $this->resolver->resolve(
            $this->getFieldStub(),
            $this->contextMock,
            $this->getResolveInfoStub()
        );
    }

    /**
     * Returns stub for Field
     *
     * @return MockObject|Field
     */
    private function getFieldStub(): Field
    {
        /** @var MockObject|Field $fieldMock */
        $fieldMock = $this->createMock(Field::class);

        return $fieldMock;
    }

    /**
     * Returns stub for ResolveInfo
     *
     * @return MockObject|ResolveInfo
     */
    private function getResolveInfoStub(): ResolveInfo
    {
        /** @var MockObject|ResolveInfo $resolveInfoMock */
        $resolveInfoMock = $this->createMock(ResolveInfo::class);

        return $resolveInfoMock;
    }
}
