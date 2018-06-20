<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Block\GlobalSearch;
use Magento\Backend\Model\GlobalSearch\SearchEntity;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for @see GlobalSearch.
 */
class GlobalSearchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GlobalSearch
     */
    private $globalSearch;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Backend\Model\GlobalSearch\SearchEntityFactory
     */
    private $searchEntityFactory;

    /**
     * @var array
     */
    private $entityResources = [
        'Products' => \Magento\Catalog\Controller\Adminhtml\Product::ADMIN_RESOURCE,
        'Orders' => \Magento\Sales\Controller\Adminhtml\Order::ADMIN_RESOURCE,
        'Customers' => \Magento\Customer\Controller\Adminhtml\Index::ADMIN_RESOURCE,
        'Pages' => \Magento\Cms\Controller\Adminhtml\Page\Index::ADMIN_RESOURCE,
    ];

    /**
     * @var array
     */
    private $entityPaths = [
        'Products' => 'catalog/product/index/',
        'Orders' => 'sales/order/index/',
        'Customers' => 'customer/index/index',
        'Pages' => 'cms/page/index/',
    ];

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->authorization = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $context = $this->createMock(\Magento\Backend\Block\Template\Context::class);

        $context->expects($this->atLeastOnce())->method('getAuthorization')->willReturn($this->authorization);
        $context->expects($this->atLeastOnce())->method('getUrlBuilder')->willReturn($this->urlBuilder);

        $this->searchEntityFactory = $this->createMock(\Magento\Backend\Model\GlobalSearch\SearchEntityFactory::class);

        $this->globalSearch = $objectManager->getObject(
            GlobalSearch::class,
            [
                'context' => $context,
                'searchEntityFactory' => $this->searchEntityFactory,
                'entityResources' => $this->entityResources,
                'entityPaths' => $this->entityPaths,
            ]
        );
    }

    /**
     * @param array $results
     * @param int $expectedEntitiesQty
     *
     * @dataProvider getEntitiesToShowDataProvider
     */
    public function testGetEntitiesToShow(array $results, int $expectedEntitiesQty)
    {
        $searchEntity = $this->createMock(SearchEntity::class);

        $this->authorization->expects($this->exactly(count($results)))->method('isAllowed')
            ->willReturnOnConsecutiveCalls($results[0], $results[1], $results[2], $results[3]);
        $this->urlBuilder->expects($this->exactly($expectedEntitiesQty))
            ->method('getUrl')->willReturn('some/url/is/here');
        $this->searchEntityFactory->expects($this->exactly($expectedEntitiesQty))
            ->method('create')->willReturn($searchEntity);

        $searchEntity->expects($this->exactly($expectedEntitiesQty))->method('setId');
        $searchEntity->expects($this->exactly($expectedEntitiesQty))->method('setTitle');
        $searchEntity->expects($this->exactly($expectedEntitiesQty))->method('setUrl');

        $this->assertSame($expectedEntitiesQty, count($this->globalSearch->getEntitiesToShow()));
    }

    public function getEntitiesToShowDataProvider()
    {
        return [
            [
                [true, false, true, false],
                2,
            ],
            [
                [true, true, true, true],
                4,
            ],
            [
                [false, false, false, false],
                0,
            ],
        ];
    }
}
