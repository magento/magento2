<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Relations;

require_once __DIR__ . '/../_files/Child.php';

class RuntimeTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @return array
     */
    public function getParentsDataProvider()
    {
        return [
            ['Magento\Test\Di\DiInterface', []],
            ['Magento\Test\Di\DiParent', [null, 'Magento\Test\Di\DiInterface']],
            ['Magento\Test\Di\Child', ['Magento\Test\Di\DiParent', 'Magento\Test\Di\ChildInterface']]
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
