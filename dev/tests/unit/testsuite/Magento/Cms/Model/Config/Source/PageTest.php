<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Model\Config\Source;

/**
 * Class PageTest
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageRepositoryMock;

    /**
     * @var \Magento\Cms\Api\PageCriteriaInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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

        $this->pageRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Cms\Api\PageRepositoryInterface',
            [],
            '',
            false,
            true,
            true,
            ['getList']
        );
        $this->pageCriteriaFactoryMock = $this->getMock(
            'Magento\Cms\Api\PageCriteriaInterfaceFactory',
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
        $pageCollectionMock = $this->getMockForAbstractClass(
            'Magento\Cms\Api\Data\PageCollectionInterface',
            [],
            '',
            false,
            true,
            true,
            ['toOptionIdArray']
        );
        $pageCriteriaMock = $this->getMockForAbstractClass(
            'Magento\Cms\Api\PageCriteriaInterface',
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
