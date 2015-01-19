<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource\Fulltext;

use Magento\TestFramework\Helper\ObjectManager;
use PHPUnit_Framework_TestCase;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext\Collection
     */
    private $model;

    /**
     * setUp method for CollectionTest
     */
    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $storeManager = $this->getStoreManager();
        $universalFactory = $this->getUniversalFactory();
        $scopeConfig = $this->getScopeConfig();
        $requestBuilder = $this->getRequestBuilder();

        $this->model = $helper->getObject(
            'Magento\CatalogSearch\Model\Resource\Fulltext\Collection',
            [
                'storeManager' => $storeManager,
                'universalFactory' => $universalFactory,
                'scopeConfig' => $scopeConfig,
                'requestBuilder' => $requestBuilder
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
        $connection = $this->getMockBuilder('Zend_Db_Adapter_Abstract')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $entity = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->setMethods(['getReadConnection', 'getTable', 'getDefaultAttributes', 'getEntityTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getReadConnection')
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
    protected function getRequestBuilder()
    {
        $requestBuilder = $this->getMockBuilder('Magento\Framework\Search\Request\Builder')
            ->setMethods(['bind', 'setRequestName'])
            ->disableOriginalConstructor()
            ->getMock();
        $requestBuilder->expects($this->once())
            ->method('bind')
            ->withConsecutive(['price_dynamic_algorithm', 1]);
        $requestBuilder->expects($this->once())
            ->method('setRequestName')
            ->withConsecutive(['quick_search_container'])
            ->willThrowException(new \Exception('setRequestName', 333));

        return $requestBuilder;
    }
}
