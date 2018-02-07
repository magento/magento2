<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

/**
 * Base class for Collection tests.
 *
 * Contains helper methods to get commonly used mocks used for collection tests.
 **/
class BaseCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get Mocks for StoreManager so Collection can be used.
     *
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
     * Get mock for UniversalFactory so Collection can be used.
     *
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
}
