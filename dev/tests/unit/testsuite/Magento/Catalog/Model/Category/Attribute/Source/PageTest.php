<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Source;

use Magento\Cms\Model\Resource\Block\CollectionFactory;
use Magento\TestFramework\Helper\ObjectManager;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $testArray = ['test1', ['test1']];

    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Source\Page
     */
    private $model;

    public function testGetAllOptions()
    {
        $assertArray = $this->testArray;
        array_unshift($assertArray, ['value' => '', 'label' => __('Please select a static block.')]);
        $this->assertEquals($assertArray, $this->model->getAllOptions());
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Category\Attribute\Source\Page',
            [
                'blockCollectionFactory' => $this->getMockedBlockCollectionFactory()
            ]
        );
    }

    /**
     * @return \Magento\Cms\Model\Resource\Block\CollectionFactory
     */
    private function getMockedBlockCollectionFactory()
    {
        $mockedCollection = $this->getMockedCollection();

        $mockBuilder = $this->getMockBuilder('Magento\Cms\Model\Resource\Block\Grid\CollectionFactory');
        $mock = $mockBuilder->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($mockedCollection));

        return $mock;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    private function getMockedCollection()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\Data\Collection');
        $mock = $mockBuilder->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($mock));

        $mock->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnValue($this->testArray));

        return $mock;
    }
}
