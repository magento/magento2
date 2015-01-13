<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Relations;

require_once __DIR__ . '/../../../_files/Child.php';
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
            ['Magento\Test\Di\DiInterface', []],
            ['Magento\Test\Di\DiParent', [null, 'Magento\Test\Di\DiInterface']],
            ['Magento\Test\Di\Child', ['Magento\Test\Di\DiParent', 'Magento\Test\Di\ChildInterface']]
        ];
    }
}
