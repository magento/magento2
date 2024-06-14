<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Elasticsearch\Model\ResourceModel\Engine;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
    /**
     * @var Engine
     */
    private $model;

    /**
     * @var Visibility|MockObject
     */
    protected $catalogProductVisibility;

    /**
     * @var IndexScopeResolver|MockObject
     */
    private $indexScopeResolver;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIfNullSql'])
            ->getMockForAbstractClass();
        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', 'getTableName'])
            ->getMock();
        $resource->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $resource->expects($this->any())
            ->method('getTableName')
            ->willReturnArgument(0);

        $this->catalogProductVisibility = $this->getMockBuilder(Visibility::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVisibleInSiteIds'])
            ->getMock();

        $this->indexScopeResolver = $this->getMockBuilder(
            IndexScopeResolver::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['getVisibleInSiteIds'])
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Engine::class,
            [
                'catalogProductVisibility' => $this->catalogProductVisibility,
                'indexScopeResolver' => $this->indexScopeResolver
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
        $this->assertEquals($expected, $this->model->prepareEntityIndex($data['index'], $data['separator']));
    }

    /**
     *  Test allowAdvancedIndex method
     */
    public function testAllowAdvancedIndex()
    {
        $this->assertFalse($this->model->allowAdvancedIndex());
    }

    /**
     *  Test isAvailable method
     */
    public function testIsAvailable()
    {
        $this->assertTrue($this->model->isAvailable());
    }

    /**
     *  Test getAllowedVisibility method
     *  Will return getVisibleInSiteIds array
     */
    public function testGetAllowedVisibility()
    {
        $this->catalogProductVisibility->expects($this->once())
            ->method('getVisibleInSiteIds')
            ->willReturn([3, 2, 4]);

        $this->assertEquals([3, 2, 4], $this->model->getAllowedVisibility());
    }

    /**
     *  Test processAttributeValue method
     */
    public function testProcessAttributeValue()
    {
        $this->assertEquals(1, $this->model->processAttributeValue('attribute', 1));
    }

    /**
     * @return array
     */
    public static function prepareEntityIndexDataProvider()
    {
        return [
            [
                [],
                [
                    'index' => [],
                    'separator' => ' ',
                ],
            ],
        ];
    }
}
