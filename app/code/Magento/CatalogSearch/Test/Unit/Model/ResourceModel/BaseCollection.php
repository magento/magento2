<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Base class for Collection tests.
 *
 * Contains helper methods to get commonly used mocks used for collection tests.
 *
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 **/
class BaseCollection extends TestCase
{
    /**
     * Get Mocks for StoreManager so Collection can be used.
     *
     * @return MockObject
     */
    protected function getStoreManager()
    {
        $store = $this->getMockBuilder(Store::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        return $storeManager;
    }

    /**
     * Get mock for UniversalFactory so Collection can be used.
     *
     * @return MockObject
     */
    protected function getUniversalFactory()
    {
        $connection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['select'])
            ->getMockForAbstractClass();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())->method('select')->willReturn($select);

        $entity = $this->getMockBuilder(AbstractEntity::class)
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

        $universalFactory = $this->getMockBuilder(UniversalFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory->expects($this->once())
            ->method('create')
            ->willReturn($entity);

        return $universalFactory;
    }
}
