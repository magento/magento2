<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Relations;

use Magento\Framework\ObjectManager\Relations\Runtime;
use Magento\Test\Di\Child;
use Magento\Test\Di\ChildInterface;
use Magento\Test\Di\DiInterface;
use Magento\Test\Di\DiParent;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../_files/Child.php';

class RuntimeTest extends TestCase
{
    /**
     * @var Runtime
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new Runtime();
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

    /**
     * @return array
     */
    public function getParentsDataProvider()
    {
        return [
            [DiInterface::class, []],
            [DiParent::class, [null, DiInterface::class]],
            [Child::class, [DiParent::class, ChildInterface::class]]
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
