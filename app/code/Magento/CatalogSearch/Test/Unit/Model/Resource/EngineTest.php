<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Resource;


use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Resource\Engine
     */
    private $target;

    /**
     * @var Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;
    /**
     * @var \Magento\Framework\Model\Resource\Db\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource = $resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResources')
            ->willReturn($this->resource);
        $this->connection = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getIfNullSql'])
            ->getMockForAbstractClass();
        $resource->expects($this->any())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_WRITE_RESOURCE)
            ->will($this->returnValue($this->connection));

        $objectManager = new ObjectManager($this);
        $this->target = $objectManager->getObject(
            '\Magento\CatalogSearch\Model\Resource\Engine',
            [
                'context' => $this->context,
            ]
        );
        $this->target;
    }

    /**
     * @dataProvider saveEntityIndexesDataProvider
     */
    public function testSaveEntityIndexes($storeId, $entityIndexes, $expected)
    {
        if ($expected) {
            $this->connection->expects($this->once())
                ->method('insertOnDuplicate')
                ->with(null, $expected, ['data_index'])
                ->willReturnSelf();
        }
        $this->target->saveEntityIndexes($storeId, $entityIndexes);
    }

    public function saveEntityIndexesDataProvider()
    {
        return [
            'empty' => [
                null,
                [],
                []
            ],
            'correctData' => [
                13,
                [
                    28 => [
                        123 => 'Value of 123',
                        845 => 'Value of 845',
                        'options' => 'Some | Index | Value'
                    ]
                ],
                [
                    [
                        'product_id' => 28,
                        'attribute_id' => 123,
                        'store_id' => 13,
                        'data_index' => 'Value of 123'
                    ],
                    [
                        'product_id' => 28,
                        'attribute_id' => 845,
                        'store_id' => 13,
                        'data_index' => 'Value of 845'
                    ],
                    [
                    'product_id' => 28,
                    'attribute_id' => 0,
                    'store_id' => 13,
                    'data_index' => 'Some | Index | Value'
                ]

            ]
            ]
        ];
    }
}
