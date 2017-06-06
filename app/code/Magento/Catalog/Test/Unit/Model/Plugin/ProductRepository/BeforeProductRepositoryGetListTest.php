<?php

namespace Magento\Catalog\Test\Unit\Model\Plugin\ProductRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Plugin\ProductRepository\BeforeProductRepositoryGetList;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class BeforeProductRepositoryGetListTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    private $objectManager;

    /** @var  StoreManagerInterface */
    private $storeManagerMock;

    /** @var  ProductRepositoryInterface */
    private $subjectMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->subjectMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->setMethods([])
            ->getMock();
    }
    
    public function testBeforeGetList()
    {
        $store = $this->objectManager->getObject(Store::class);
        $store->setStoreId(1);
        $this->storeManagerMock->method('getStore')->willReturn($store);

        $plugin = new BeforeProductRepositoryGetList(
            $this->storeManagerMock,
            $this->objectManager->getObject(FilterGroupBuilder::class),
            $this->objectManager->getObject(FilterBuilder::class)
        );
        $args = $plugin->beforeGetList($this->subjectMock, new SearchCriteria());
        self::assertEquals(
            [
                new SearchCriteria(
                    [
                        SearchCriteria::FILTER_GROUPS => [
                            new FilterGroup(
                                [
                                    FilterGroup::FILTERS => [
                                        new Filter(
                                            [
                                                Filter::KEY_FIELD => 'store_id',
                                                Filter::KEY_CONDITION_TYPE => 'eq',
                                                Filter::KEY_VALUE => '1'
                                            ]
                                        )
                                    ]
                                ]
                            )
                        ]
                    ]
                )
            ],
            $args
        );
    }
}
