<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Framework\Object;
use Magento\TestFramework\Helper\ObjectManager;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Group
     */
    private $model;

    public function testHasSystemAttributes()
    {
        $this->model->setId(1);
        $this->assertTrue($this->model->hasSystemAttributes());
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Group',
            [
                'attributeCollectionFactory' => $this->getMockedCollectionFactory()
            ]
        );
    }

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    private function getMockedCollectionFactory()
    {
        $mockedCollection = $this->getMockedCollection();

        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory');
        $mock = $mockBuilder->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($mockedCollection));

        return $mock;
    }

    /**
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    private function getMockedCollection()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\Attribute\Collection');
        $mock = $mockBuilder->disableOriginalConstructor()
            ->getMock();

        $item = new Object();
        $item->setIsUserDefine(false);

        $mock->expects($this->any())
            ->method('setAttributeGroupFilter')
            ->will($this->returnValue($mock));
        $mock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$item])));

        return $mock;
    }
}
