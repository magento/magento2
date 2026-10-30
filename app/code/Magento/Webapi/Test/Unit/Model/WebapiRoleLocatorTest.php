<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Authorization\Model\CompositeUserContext;
use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Webapi\Model\WebapiRoleLocator;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class WebapiRoleLocatorTest extends TestCase
{
    /**
     * @var WebapiRoleLocator
     */
    private $locator;

    /**
     * @var CompositeUserContext|Stub
     */
    private $userContext;

    /**
     * @var RoleCollectionFactory|Stub
     */
    private $roleCollectionFactory;

    /**
     * @var array<string, int|null>
     */
    private array $rolesByUser = [];

    /**
     * @var int
     */
    private int $userId = 0;

    /**
     * @var int
     */
    private int $userType = 0;

    /**
     * @var int
     */
    private int $roleQueries = 0;

    protected function setUp(): void
    {
        $this->userContext = $this->createStub(CompositeUserContext::class);
        $this->userContext->method('getUserId')->willReturnCallback(fn () => $this->userId);
        $this->userContext->method('getUserType')->willReturnCallback(fn () => $this->userType);
        $this->roleCollectionFactory = $this->createStub(RoleCollectionFactory::class);
        $this->roleCollectionFactory->method('create')
            ->willReturnCallback(fn () => $this->createRoleCollection());

        $this->locator = new WebapiRoleLocator($this->userContext, $this->roleCollectionFactory);
    }

    public function testNoRoleId(): void
    {
        $this->setUser(5, 2);
        $this->rolesByUser = ['2|5' => null];

        $this->assertNull($this->locator->getAclRoleId());
    }

    public function testGetAclRoleId(): void
    {
        $this->setUser(5, 2);
        $this->rolesByUser = ['2|5' => 9];

        $this->assertEquals(9, $this->locator->getAclRoleId());
    }

    public function testRoleIdIsLoadedOncePerUser(): void
    {
        $this->setUser(5, 2);
        $this->rolesByUser = ['2|5' => 9];

        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->assertSame(1, $this->roleQueries);
    }

    public function testMissingRoleIsLoadedAgainOnNextCall(): void
    {
        $this->setUser(5, 2);
        $this->rolesByUser = ['2|5' => null];

        $this->assertNull($this->locator->getAclRoleId());
        $this->rolesByUser = ['2|5' => 9];
        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->assertSame(2, $this->roleQueries);
    }

    public function testRoleIdIsResolvedPerUserIdAndType(): void
    {
        $this->rolesByUser = ['2|5' => 9, '1|5' => 12, '2|6' => 15];

        $this->setUser(5, 2);
        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->setUser(5, 1);
        $this->assertEquals(12, $this->locator->getAclRoleId());
        $this->setUser(6, 2);
        $this->assertEquals(15, $this->locator->getAclRoleId());
        $this->setUser(5, 2);
        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->assertSame(3, $this->roleQueries);
    }

    public function testResetStateDropsLoadedRoleIds(): void
    {
        $this->setUser(5, 2);
        $this->rolesByUser = ['2|5' => 9];

        $this->assertEquals(9, $this->locator->getAclRoleId());
        $this->locator->_resetState();
        $this->rolesByUser = ['2|5' => 10];
        $this->assertEquals(10, $this->locator->getAclRoleId());
        $this->assertSame(2, $this->roleQueries);
    }

    private function setUser(int $userId, int $userType): void
    {
        $this->userId = $userId;
        $this->userType = $userType;
    }

    private function createRoleCollection(): RoleCollection
    {
        $this->roleQueries++;
        $role = $this->createStub(Role::class);
        $collection = $this->createStub(RoleCollection::class);
        $collection->method('setUserFilter')->willReturnCallback(
            function ($userId, $userType) use ($collection, $role) {
                $role->method('getId')->willReturn($this->rolesByUser[$userType . '|' . $userId] ?? null);
                return $collection;
            }
        );
        $collection->method('getFirstItem')->willReturn($role);

        return $collection;
    }
}
