<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PageTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Category\Attribute\Source\Page::class,
            [
                'blockCollectionFactory' => $this->getMockedBlockCollectionFactory()
            ]
        );
    }

    /**
     * @return \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private function getMockedBlockCollectionFactory()
    {
        $mockedCollection = $this->getMockedCollection();

        $mockBuilder = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Block\CollectionFactory::class);
        $mock = $mockBuilder->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->willReturn($mockedCollection);

        return $mock;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    private function getMockedCollection()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Framework\Data\Collection::class);
        $mock = $mockBuilder->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('load')
            ->willReturn($mock);

        $mock->expects($this->any())
            ->method('toOptionArray')
            ->willReturn($this->testArray);

        return $mock;
    }
}
