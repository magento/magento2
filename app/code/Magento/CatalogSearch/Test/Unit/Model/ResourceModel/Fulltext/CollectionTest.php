<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel\Fulltext;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_TestCase;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    private $model;

    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $filter;

    /**
     * setUp method for CollectionTest
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $scopeConfig = $this->getScopeConfig();
        $criteriaBuilder = $this->getCriteriaBuilder();
        $filterBuilder = $this->getFilterBuilder();

        $this->model = $helper->getObject(
            'Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection',
            [
                'storeManager' => $storeManager,
                'universalFactory' => $universalFactory,
                'scopeConfig' => $scopeConfig,
                'searchCriteriaBuilder' => $criteriaBuilder,
                'filterBuilder' => $filterBuilder,
            ]
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionCode 333
     * @expectedExceptionMessage setRequestName
     */
    public function testGetFacetedData()
    {
        $this->model->getFacetedData('field');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStoreManager()
    {
        $store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        return $storeManager;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getUniversalFactory()
    {
        $connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['select'])
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($select);

        $entity = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->setMethods(['getConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $entity->expects($this->exactly(2))
            ->method('getTable')
            ->willReturnArgument(0);
        $entity->expects($this->once())
            ->method('getDefaultAttributes')
            ->willReturn(['attr1', 'attr2']);
        $entity->expects($this->once())
            ->method('getEntityTable')
            ->willReturn('table');

        $universalFactory = $this->getMockBuilder('Magento\Framework\Validator\UniversalFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory->expects($this->once())
            ->method('create')
            ->willReturn($entity);

        return $universalFactory;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getScopeConfig()
    {
        $scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn(1);

        return $scopeConfig;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCriteriaBuilder()
    {
        $criteriaBuilder = $this->getMockBuilder('Magento\Framework\Api\Search\SearchCriteriaBuilder')
            ->setMethods(['addFilter', 'create', 'setRequestName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filter = new \Magento\Framework\Api\Filter();
        $this->filter->setField('price_dynamic_algorithm');
        $this->filter->setValue(1);
        $criteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with($this->filter);
        $criteria = $this->getMock('Magento\Framework\Api\Search\SearchCriteria', [], [], '', false);
        $criteriaBuilder->expects($this->once())->method('create')->willReturn($criteria);
        $criteria->expects($this->once())
            ->method('setRequestName')
            ->withConsecutive(['catalog_view_container'])
            ->willThrowException(new \Exception('setRequestName', 333));

        return $criteriaBuilder;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilterBuilder()
    {
        $filterBuilder = $this->getMock('Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $filterBuilder->expects($this->once())->method('setField')->with('price_dynamic_algorithm');
        $filterBuilder->expects($this->once())->method('setValue')->with(1);
        $filterBuilder->expects($this->once())->method('create')->willReturn($this->filter);
        return $filterBuilder;
    }
}
