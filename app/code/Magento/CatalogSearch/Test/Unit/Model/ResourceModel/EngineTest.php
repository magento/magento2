<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\Engine;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
    /**
     * @var Engine
     */
    private $target;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIfNullSql'])
            ->getMockForAbstractClass();
        $resource = $this->getMockBuilder(ResourceConnection::class)
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
            Engine::class,
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
