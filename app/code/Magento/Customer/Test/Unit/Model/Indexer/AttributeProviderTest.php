<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Indexer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Indexer\AttributeProvider;

class AttributeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Customer\Model\Resource\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var AttributeProvider
     */
    protected $object;

    public function setUp()
    {
        $this->eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection = $this->getMockBuilder('Magento\Customer\Model\Resource\Attribute\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactory = $this->getMockBuilder('Magento\Customer\Model\Resource\Attribute\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        /** @var \Magento\Customer\Model\Resource\Attribute\CollectionFactory $collectionFactory */
        $this->object = new AttributeProvider(
            $this->eavConfig,
            $collectionFactory
        );
    }

    public function testAddDynamicData()
    {
        $data = ['fields' => []];
        $attrName = 'attrName';
        $attrBackendType = 'b_type';

        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('is_used_on_grid', true)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$attribute]);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with(Customer::ENTITY)
            ->willReturn($entityType);
        $entityType->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);
        $attribute->expects($this->once())
            ->method('setEntity')
            ->with($entity)
            ->willReturnSelf();
        $attribute->expects($this->once())
            ->method('getName')
            ->willReturn($attrName);
        $attribute->expects($this->once())
            ->method('getBackendType')
            ->willReturn($attrBackendType);

        $this->assertEquals(
            ['fields' =>
                [
                    $attrName => [
                        'name' => $attrName,
                        'dataType' => $attrBackendType,
                        'type' => 'virtual',
                        'filters' => []
                    ]
                ]
            ],
            $this->object->addDynamicData($data)
        );
    }
}
