<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\ObjectManager\Test\Unit\Relations;

require_once __DIR__ . '/../_files/Child.php';
class RuntimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\Relations\Runtime
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\ObjectManager\Relations\Runtime();
    }

    /**
     * @param $type
     * @param $parents
     * @dataProvider getParentsDataProvider
     */
    public function testGetParents($type, $parents)
    {
        $this->assertEquals($parents, $this->_model->getParents($type));
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
     * @dataProvider nonExistentGeneratorsDataProvider
     */
    public function testHasIfNonExists($entity)
    {
        $this->assertFalse($this->_model->has($entity));
    }

    public function nonExistentGeneratorsDataProvider()
    {
        return [
            [\Magento\Test\Module\Model\Item\Factory::class],
            [\Magento\Test\Module\Model\Item\Proxy::class],
            [\Magento\Test\Module\Model\Item\Interceptor::class],
            [\Magento\Test\Module\Model\Item\Mapper::class],
            [\Magento\Test\Module\Model\Item\SearchResults::class]
        ];
    }
}
