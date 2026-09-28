<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Test\Unit;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\Wishlist\Config;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\WishlistGraphQl\Model\Resolver\WishlistResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WishlistResolverTest extends TestCase
{
    use MockCreationTrait;

    private const STUB_CUSTOMER_ID = 1;

    /**
     * @var MockObject|ContextInterface
     */
    private $contextMock;

    /**
     * @var MockObject|WishlistFactory
     */
    private $wishlistFactoryMock;

    /**
     * @var MockObject|Wishlist
     */
    private $wishlistMock;

    /**
     * @var WishlistResolver
     */
    private $resolver;

    /**
     * @var Config|MockObject
     */
    private $wishlistConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMockWithReflection(
            ContextInterface::class,
            ['getUserId']
        );

        $this->wishlistFactoryMock = $this->createPartialMock(WishlistFactory::class, ['create']);

        $this->wishlistMock = $this->createPartialMockWithReflection(
            Wishlist::class,
            ['getSharingCode', 'getUpdatedAt', 'loadByCustomerId', 'getItemsCount', 'getName']
        );

        $this->wishlistConfigMock = $this->createMock(Config::class);

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(WishlistResolver::class, [
            'wishlistFactory' => $this->wishlistFactoryMock,
            'wishlistConfig' => $this->wishlistConfigMock,
        ]);
    }

    /**
     * Verify authorization exception is thrown for guest user
     */
    public function testThrowExceptionWhenUserNotAuthorized(): void
    {
        $this->wishlistConfigMock->method('isEnabled')->willReturn(true);
        $this->contextMock->method('getUserId')->willReturn(0);

        $this->expectException(GraphQlAuthorizationException::class);
        $this->wishlistFactoryMock->expects($this->never())->method('create');

        $this->resolver->resolve(
            $this->getFieldStub(),
            $this->contextMock,
            $this->getResolveInfoStub()
        );
    }

    /**
     * Verify wishlist is loaded and created for authorized customer
     */
    public function testFactoryCreatesWishlistByCustomerId(): void
    {
        $this->wishlistConfigMock->method('isEnabled')->willReturn(true);
        $this->contextMock->method('getUserId')->willReturn(self::STUB_CUSTOMER_ID);

        $this->wishlistMock->expects($this->once())
            ->method('loadByCustomerId')
            ->with(self::STUB_CUSTOMER_ID, true)
            ->willReturnSelf();
        $this->wishlistMock->method('getSharingCode')->willReturn('sharing_code');
        $this->wishlistMock->method('getUpdatedAt')->willReturn('2026-01-01 00:00:00');
        $this->wishlistMock->method('getItemsCount')->willReturn(0);
        $this->wishlistMock->method('getName')->willReturn('My Wish List');

        $this->wishlistFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->wishlistMock);

        $result = $this->resolver->resolve(
            $this->getFieldStub(),
            $this->contextMock,
            $this->getResolveInfoStub()
        );

        $this->assertSame([
            'sharing_code' => 'sharing_code',
            'updated_at' => '2026-01-01 00:00:00',
            'items_count' => 0,
            'name' => 'My Wish List',
            'model' => $this->wishlistMock,
        ], $result);
    }

    /**
     * Returns stub for Field
     *
     * @return MockObject|Field
     */
    private function getFieldStub(): Field
    {
        return $this->createMock(Field::class);
    }

    /**
     * Returns stub for ResolveInfo
     *
     * @return MockObject|ResolveInfo
     */
    private function getResolveInfoStub(): ResolveInfo
    {
        return $this->createMock(ResolveInfo::class);
    }
}
