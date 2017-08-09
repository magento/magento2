<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Relations;

require_once __DIR__ . '/../_files/Child.php';

class RuntimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Relations\Runtime
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\ObjectManager\Relations\Runtime();
    }

    /**
     * @param $type
     * @param $parents
     * @dataProvider getParentsDataProvider
     */
    public function testGetParents($type, $parents)
    {
        $this->assertEquals($parents, $this->model->getParents($type));
    }

    public function getParentsDataProvider()
    {
        return [
            [\Magento\Test\Di\DiInterface::class, []],
            [\Magento\Test\Di\DiParent::class, [null, \Magento\Test\Di\DiInterface::class]],
            [\Magento\Test\Di\Child::class, [\Magento\Test\Di\DiParent::class, \Magento\Test\Di\ChildInterface::class]]
        ];
    }

    /**
     * @param $entity
     */
    public function testHasIfNonExists()
    {
        $this->assertFalse($this->model->has(\NonexistentClass::class));
    }
}
