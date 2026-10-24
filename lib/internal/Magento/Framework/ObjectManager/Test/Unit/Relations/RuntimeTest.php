<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Relations;

use Magento\Framework\ObjectManager\Relations\Runtime;
use Magento\Test\Di\Child;
use Magento\Test\Di\ChildInterface;
use Magento\Test\Di\DiInterface;
use Magento\Test\Di\DiParent;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @param $parents     */
    #[DataProvider('getParentsDataProvider')]
    public function testGetParents($type, $parents)
    {
        $this->assertEquals($parents, $this->model->getParents($type));
    }

    /**
     * @return array
     */
    public static function getParentsDataProvider()
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
