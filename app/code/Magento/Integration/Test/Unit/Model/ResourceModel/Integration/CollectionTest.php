<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\ResourceModel\Integration;

/**
 * Unit test for \Magento\Integration\Model\ResourceModel\Integration\Collection
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $select;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Integration\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $collection;

    protected function setUp(): void
    {
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getConnection'])
            ->getMockForAbstractClass();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            \Magento\Integration\Model\ResourceModel\Integration\Collection::class,
            ['resource' => $resource]
        );

        $this->collection = $this->getMockBuilder(
            \Magento\Integration\Model\ResourceModel\Integration\Collection::class
        )->setConstructorArgs($arguments)
            ->setMethods(['addFilter', '_translateCondition', 'getMainTable'])
            ->getMock();
    }

    public function testAddUnsecureUrlsFilter()
    {
        $this->collection->expects($this->at(0))
            ->method('_translateCondition')
            ->with('endpoint', ['like' => 'http:%'])
            ->willReturn('endpoint like \'http:%\'');

        $this->collection->expects($this->at(1))
            ->method('_translateCondition')
            ->with('identity_link_url', ['like' => 'http:%'])
            ->willReturn('identity_link_url like \'http:%\'');

        $this->select->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('(endpoint like \'http:%\') OR (identity_link_url like \'http:%\')'),
                $this->equalTo(null),
                $this->equalTo(\Magento\Framework\DB\Select::TYPE_CONDITION)
            );

        $this->collection->addUnsecureUrlsFilter();
    }
}
