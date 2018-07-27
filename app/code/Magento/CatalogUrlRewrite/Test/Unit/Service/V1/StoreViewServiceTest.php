<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Service\V1;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class StoreViewServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Service\V1\StoreViewService */
    protected $storeViewService;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $connection;

    /** @var  \Magento\Framework\Db\Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $select;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['select', 'from', 'where', 'join'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->connection);

        $this->storeViewService = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Service\V1\StoreViewService',
            [
                'eavConfig' => $this->config,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * @return array
     */
    public function isRootCategoryForStoreDataProvider()
    {
        return [
            [1, 1, 1, true],
            [1, 2, 1, false],
            [2, 0, 1, false],
        ];
    }

    /**
     * @return array
     */
    public function overriddenUrlKeyForStoreDataProvider()
    {
        return [
            [1, [1, 2], true],
            [1, [2, 3], false],
            [1, [], false],
        ];
    }

    /**
     * @dataProvider overriddenUrlKeyForStoreDataProvider
     * @param int $storeId
     * @param array $fetchedStoreIds
     * @param bool $result
     */
    public function testDoesEntityHaveOverriddenUrlKeyForStore($storeId, $fetchedStoreIds, $result)
    {
        $entityType = 'entity_type';
        $productId = 'product_id';
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getBackendTable', 'getId', 'getEntity'])
            ->getMockForAbstractClass();
        $this->config->expects($this->once())->method('getAttribute')->with($entityType, 'url_key')
            ->will($this->returnValue($attribute));
        $entity = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->exactly(2))->method('getEntity')->willReturn($entity);
        $entity->expects($this->once())->method('getEntityTable')->will($this->returnValue('entity_table'));
        $entity->expects($this->once())->method('getLinkField')->willReturn('link_field');
        $attribute->expects($this->once())->method('getBackendTable')->will($this->returnValue('backend_table'));
        $attribute->expects($this->once())->method('getId')->will($this->returnValue('attribute-id'));
        $this->select->expects($this->once())->method('from')->with(['e' => 'entity_table'], [])
            ->will($this->returnSelf());
        $this->select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->select->expects($this->once())->method('join')->with(
            ['e_attr' => 'backend_table'],
            "e.link_field = e_attr.link_field",
            'store_id'
        )->will($this->returnSelf());
        $this->connection->expects($this->once())->method('select')->will($this->returnValue($this->select));
        $this->connection->expects($this->once())->method('fetchCol')->will($this->returnValue($fetchedStoreIds));

        $this->assertEquals(
            $result,
            $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore($storeId, $productId, $entityType)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot retrieve attribute for entity type "invalid_type"
     */
    public function testInvalidAttributeRetrieve()
    {
        $invalidEntityType = 'invalid_type';
        $this->config->expects($this->once())->method('getAttribute')->will($this->returnValue(false));

        $this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore(1, 1, $invalidEntityType);
    }
}
