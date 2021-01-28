<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class EngineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\ResourceModel\Engine
     */
    private $target;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIfNullSql'])
            ->getMockForAbstractClass();
        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $resource->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $objectManager = new ObjectManager($this);
        $this->target = $objectManager->getObject(
            \Magento\CatalogSearch\Model\ResourceModel\Engine::class,
            [
                'resource' => $resource,
            ]
        );
    }

    /**
     * @param null|string $expected
     * @param array $data
     * @dataProvider prepareEntityIndexDataProvider
     */
    public function testPrepareEntityIndex($expected, array $data)
    {
        $this->assertEquals($expected, $this->target->prepareEntityIndex($data['index'], $data['separator']));
    }

    /**
     * @return array
     */
    public function prepareEntityIndexDataProvider()
    {
        return [
            [
                [],
                [
                    'index' => [],
                    'separator' => '--'
                ],
            ],
            [
                ['element1','element2','element3--element4'],
                [
                    'index' => [
                        'element1',
                        'element2',
                        [
                            'element3',
                            'element4',
                        ],
                    ],
                    'separator' => '--'
                ]
            ]
        ];
    }
}
