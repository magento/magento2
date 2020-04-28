<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Definition;

use Magento\Setup\Module\Di\Definition\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $model;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * Instance name
     */
    const INSTANCE_1 = 'Class_Name_1';

    /**
     * Instance name
     */
    const INSTANCE_2 = 'Class_Name_2';

    /**
     * Returns initialized argument data
     *
     * @return array
     */
    private function getArgument()
    {
        return ['argument' => ['configuration', 'array', true, null]];
    }

    /**
     * Returns initialized expected definitions for most cases
     *
     * @return array
     */
    private function getExpectedDefinition()
    {
        return [self::INSTANCE_1 => $this->getArgument()];
    }

    protected function setUp(): void
    {
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods([])->getMock();
        $this->model = new Collection();
    }

    public function testAddDefinition()
    {
        $this->model->addDefinition(self::INSTANCE_1, $this->getArgument());
        $this->assertEquals($this->getExpectedDefinition(), $this->model->getCollection());
    }

    public function testInitialize()
    {
        $this->model->initialize([self::INSTANCE_1 => $this->getArgument()]);
        $this->assertEquals($this->getExpectedDefinition(), $this->model->getCollection());
    }

    public function testHasInstance()
    {
        $this->model->addDefinition(self::INSTANCE_1, $this->getArgument());
        $this->assertTrue($this->model->hasInstance(self::INSTANCE_1));
    }

    public function testGetInstancesNamesList()
    {
        $this->model->addDefinition(self::INSTANCE_1, $this->getArgument());
        $this->assertEquals([self::INSTANCE_1], $this->model->getInstancesNamesList());
    }

    public function testGetInstanceArguments()
    {
        $this->model->addDefinition(self::INSTANCE_1, $this->getArgument());
        $this->assertEquals($this->getArgument(), $this->model->getInstanceArguments(self::INSTANCE_1));
    }

    public function testAddCollection()
    {
        $this->model->addDefinition(self::INSTANCE_1, $this->getArgument());
        $this->collectionMock->expects($this->any())->method('getCollection')
            ->willReturn([self::INSTANCE_2 => $this->getArgument()]);
        $this->model->addCollection($this->collectionMock);
        $this->assertEquals(
            [self::INSTANCE_1 => $this->getArgument(), self::INSTANCE_2 => $this->getArgument()],
            $this->model->getCollection()
        );
    }
}
