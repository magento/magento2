<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\NewRelicReporting\Model\Users;
use Magento\NewRelicReporting\Model\ResourceModel\Users as UsersResource;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Test for Users model
 *
 * @covers \Magento\NewRelicReporting\Model\Users
 */
class UsersTest extends TestCase
{
    /**
     * Create Users instance with minimal required dependencies
     * @return Users
     * @throws LocalizedException | Exception
     */
    private function createUsers(): Users
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $resource = $this->createMock(UsersResource::class);

        return new Users($context, $registry, $resource);
    }

    /**
     * Test that Users extends AbstractModel
     *
     * @return void
     * @throws Exception | LocalizedException
     */
    public function testItExtendsAbstractModel(): void
    {
        $users = $this->createUsers();
        $this->assertInstanceOf(AbstractModel::class, $users);
    }

    /**
     * Test that Users initializes the correct resource model
     *
     * @return void
     * @throws Exception | LocalizedException
     */
    public function testItInitializesResourceModel(): void
    {
        $users = $this->createUsers();

        $reflection = new ReflectionClass($users);
        $resourceNameProperty = $reflection->getProperty('_resourceName');
        $resourceNameProperty->setAccessible(true);

        $this->assertEquals(
            UsersResource::class,
            $resourceNameProperty->getValue($users)
        );
    }
}
