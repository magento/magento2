<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\ResourceModel\Users;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\NewRelicReporting\Model\Users;
use Magento\NewRelicReporting\Model\ResourceModel\Users as UsersResource;
use Magento\NewRelicReporting\Model\ResourceModel\Users\Collection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Unit test for Users Collection
 *
 * @covers \Magento\NewRelicReporting\Model\ResourceModel\Users\Collection
 */
class CollectionTest extends TestCase
{
    /**
     * Test that Collection extends AbstractCollection
     *
     * @return void
     */
    public function testExtendsAbstractCollection(): void
    {
        $reflection = new ReflectionClass(Collection::class);
        $this->assertTrue($reflection->isSubclassOf(AbstractCollection::class));
    }

    /**
     * Test that _construct calls _init with correct model and resource model
     *
     * @return void
     * @throws ReflectionException
     */
    public function testConstructInitializesCorrectModelAndResource(): void
    {
        // Create a partial mock that only mocks the _init method
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['_init'])
            ->getMock();

        // Verify _init is called once with the correct parameters
        $collection->expects($this->once())
            ->method('_init')
            ->with(
                $this->equalTo(Users::class),
                $this->equalTo(UsersResource::class)
            );

        // Call the protected _construct method via reflection
        $reflection = new ReflectionClass($collection);
        $constructMethod = $reflection->getMethod('_construct');
        $constructMethod->invoke($collection);
    }
}
