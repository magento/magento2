<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Config\Source;

/**
 * Class PageTest
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Model\PageRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageRepositoryMock;

    /**
     * @var \Magento\Cms\Model\Resource\PageCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageCriteriaFactoryMock;

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
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->pageRepositoryMock = $this->getMock(
            'Magento\Cms\Model\PageRepository',
            [],
            [],
            '',
            false
        );
        $this->pageCriteriaFactoryMock = $this->getMock(
            'Magento\Cms\Model\Resource\PageCriteriaFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->page = $objectManager->getObject(
            'Magento\Cms\Model\Config\Source\Page',
            [
                'pageRepository' => $this->pageRepositoryMock,
                'pageCriteriaFactory' => $this->pageCriteriaFactoryMock
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
            'Magento\Cms\Model\Resource\Page\Collection',
            [],
            [],
            '',
            false
        );
        $pageCriteriaMock = $this->getMock(
            'Magento\Cms\Model\Resource\PageCriteria',
            [],
            [],
            '',
            false
        );

        $this->pageRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($pageCriteriaMock)
            ->will($this->returnValue($pageCollectionMock));

        $this->pageCriteriaFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($pageCriteriaMock));

        $pageCollectionMock->expects($this->once())
            ->method('toOptionIdArray')
            ->will($this->returnValue('return-value'));

        $this->assertEquals('return-value', $this->page->toOptionArray());
    }
}
