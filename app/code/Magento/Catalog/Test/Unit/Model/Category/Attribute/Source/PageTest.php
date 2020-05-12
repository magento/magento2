<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Source;

use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    /**
     * @var array
     */
    private $testArray = ['test1', ['test1']];

    /**
     * @var Page
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
            Page::class,
            [
                'blockCollectionFactory' => $this->getMockedBlockCollectionFactory()
            ]
        );
    }

    /**
     * @return CollectionFactory
     */
    private function getMockedBlockCollectionFactory()
    {
        $mockedCollection = $this->getMockedCollection();

        $mockBuilder = $this->getMockBuilder(CollectionFactory::class);
        $mock = $mockBuilder->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('create')
            ->willReturn($mockedCollection);

        return $mock;
    }

    /**
     * @return Collection
     */
    private function getMockedCollection()
    {
        $mockBuilder = $this->getMockBuilder(Collection::class);
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
