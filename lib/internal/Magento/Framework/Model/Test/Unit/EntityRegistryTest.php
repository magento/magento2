<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit;

use Magento\Framework\Model\EntityRegistry;

/**
 * Class EntityRegistryTest
 */
class EntityRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityRegistry
     */
    protected $entityRegistry;

    protected function setUp()
    {
        $this->entityRegistry = new EntityRegistry();
    }

    public function testRegister()
    {
        $entity = new \stdClass();
        $entity->test = 1;
        $entityType = "Test";
        $identifier = 42;
        $this->assertNull($this->entityRegistry->retrieve($entityType, $identifier));
        $this->entityRegistry->register($entityType, $identifier, $entity);
        $this->assertEquals($entity, $this->entityRegistry->retrieve($entityType, $identifier));
        $this->assertTrue($this->entityRegistry->remove($entityType, $identifier));
        $this->assertNull($this->entityRegistry->retrieve($entityType, $identifier));
    }
}
