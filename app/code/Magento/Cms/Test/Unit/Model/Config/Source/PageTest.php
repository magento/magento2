<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Config\Source;

/**
 * Class PageTest
 */
class PageTest extends \PHPUnit_Framework_TestCase
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

        $this->collectionFactory = $this->getMock(
            'Magento\Cms\Model\ResourceModel\Page\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->page = $objectManager->getObject(
            'Magento\Cms\Model\Config\Source\Page',
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
        $pageCollectionMock = $this->getMock(
            'Magento\Cms\Model\ResourceModel\Page\Collection',
            [],
            [],
            '',
            false
        );

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($pageCollectionMock));

        $pageCollectionMock->expects($this->once())
            ->method('toOptionIdArray')
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->page->toOptionArray());
    }
}
