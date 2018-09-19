<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Config\Source;

/**
 * Class PageTest
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Cms\Model\Config\Source\Page
     */
    protected $page;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->collectionFactory = $this->createPartialMock(
            \Magento\Cms\Model\ResourceModel\Page\CollectionFactory::class,
            ['create']
        );

        $this->page = $objectManager->getObject(
            \Magento\Cms\Model\Config\Source\Page::class,
            [
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    /**
     * Run test toOptionArray method
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $pageCollectionMock = $this->createMock(\Magento\Cms\Model\ResourceModel\Page\Collection::class);

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($pageCollectionMock));

        $pageCollectionMock->expects($this->once())
            ->method('toOptionIdArray')
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->page->toOptionArray());
    }
}
