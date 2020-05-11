<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model;

use Magento\CatalogUrlRewrite\Model\ObjectRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObjectRegistryTest extends TestCase
{
    /** @var ObjectRegistry */
    protected $objectRegistry;

    /** @var DataObject|MockObject */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new DataObject(['id' => 1]);
        $this->objectRegistry = (new ObjectManager($this))->getObject(
            ObjectRegistry::class,
            ['entities' => [$this->object]]
        );
    }

    public function testGet()
    {
        $this->assertEquals($this->object, $this->objectRegistry->get(1));
    }

    public function testGetNotExistObject()
    {
        $this->assertNull($this->objectRegistry->get('no-id'));
    }

    public function testGetList()
    {
        $this->assertEquals([1 => $this->object], $this->objectRegistry->getList());
    }

    public function testGetEmptyList()
    {
        $objectRegistry = (new ObjectManager($this))->getObject(
            ObjectRegistry::class,
            ['entities' => []]
        );
        $this->assertEquals([], $objectRegistry->getList());
    }
}
