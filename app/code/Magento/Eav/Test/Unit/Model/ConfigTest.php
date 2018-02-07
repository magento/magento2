<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Eav\Model\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactoryMock;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $universalFactoryMock;

    protected function setUp()
    {
        $this->cacheMock = $this->getMockBuilder('Magento\Framework\App\CacheInterface')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getFrontend', 'save', 'remove', 'clean'])
            ->getMock();
        $this->typeFactoryMock = $this->getMockBuilder('Magento\Eav\Model\Entity\TypeFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock =
            $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder('Magento\Framework\App\Cache\StateInterface')
            ->setMethods(['isEnabled', 'setEnabled', 'persist'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->universalFactoryMock = $this->getMockBuilder('Magento\Framework\Validator\UniversalFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = new Config(
            $this->cacheMock,
            $this->typeFactoryMock,
            $this->collectionFactoryMock,
            $this->stateMock,
            $this->universalFactoryMock
        );
    }

    /**
     * @param boolean $cacheEnabled
     * @param int $loadCalls
     * @param string $cachedValue
     * @dataProvider getAttributeCacheDataProvider
     * @return void
     */
    public function testGetAttributeCache($cacheEnabled, $loadCalls, $cachedValue)
    {
        $attributeCollectionMock = $this->getMockBuilder('Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setEntityTypeFilter'])
            ->getMock();
        $attributeCollectionMock
            ->expects($this->any())
            ->method('setEntityTypeFilter')
            ->will($this->returnSelf());
        $attributeCollectionMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $entityAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $factoryCalls = [
            ['Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection', [], $attributeCollectionMock],
            ['Magento\Eav\Model\Entity\Attribute', [], $entityAttributeMock],
        ];

        $this->stateMock
            ->expects($this->atLeastOnce())
            ->method('isEnabled')
            ->with(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER)
            ->willReturn($cacheEnabled);
        $this->cacheMock
            ->expects($this->exactly($loadCalls))
            ->method('load')
            ->with(Config::ATTRIBUTES_CACHE_ID)
            ->willReturn($cachedValue);

        $collectionStub = new DataObject([
            ['entity_type_code' => 'type_code_1', 'entity_type_id' => 1],
        ]);
        $this->collectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($collectionStub);

        $this->typeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn(new DataObject(['id' => 101]));

        $this->universalFactoryMock
            ->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValueMap($factoryCalls));

        $entityType = $this->getMockBuilder('\Magento\Eav\Model\Entity\Type')
            ->setMethods(['getEntity', 'setData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->getAttribute($entityType, 'attribute_code_1');
    }

    /**
     * @return array
     */
    public function getAttributeCacheDataProvider()
    {
        return [
            'cache-disabled' => [
                false,
                0,
                false,
            ],
            'cache-miss' => [
                true,
                1,
                false,
            ],
            'cached' => [
                true,
                1,
                serialize(
                    [
                        ['attribute_code' => 'attribute_code_1', 'attribute_id' => 1],
                    ]
                ),
            ],
        ];
    }

    public function testClear()
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('clean')
            ->with(
                $this->equalTo(
                    [
                        \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                        \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
                    ]
                )
            );
        $this->config->clear();
    }
}
