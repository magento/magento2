<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Unit\Model;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Search\Model\IndexScopeResolver
 */
class IndexScopeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeResolver;
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Search\Model\IndexScopeResolver
     */
    private $target;

    protected function setUp()
    {
        $this->scopeResolver = $this->getMockBuilder('\Magento\Framework\App\ScopeResolverInterface')
            ->setMethods(['resolve'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->setMethods(['getTableName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resource->expects($this->once())
            ->method('getTableName')
            ->willReturnCallback(function ($data) {
                list($table, $suffix) = $data;
                return $table . '_' . $suffix;
            });

        $objectManager = new ObjectManager($this);

        $this->target = $objectManager->getObject(
            '\Magento\Search\Model\IndexScopeResolver',
            [
                'resource' => $this->resource,
                'scopeResolver' => $this->scopeResolver,
            ]
        );
    }

    /**
     * @param string $indexName
     * @param string $expected
     * @dataProvider resolveDataProviderWithoutScopeId
     */
    public function testResolveWithoutScopeId($indexName, $expected)
    {
        $result = $this->target->resolve($indexName, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function resolveDataProviderWithoutScopeId()
    {
        return [
            [
                'index' => 'index_name',
                'expected' => 'index_name_index_default'
            ]
        ];
    }

    /**
     * @param string $indexName
     * @param string|int $scopeId
     * @param string|int $resolvedScopeId
     * @param string $expected
     * @dataProvider resolveDataProviderWithScopeId
     */
    public function testResolveWithScopeId($indexName, $scopeId, $resolvedScopeId, $expected)
    {
        $this->markTestSkipped('Should be unskipped when IndexScopeResolver would be integrated to table creation');
        $scope = $this->getMockBuilder('\Magento\Framework\App\ScopeInterface')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $scope->expects($this->once())
            ->method('getId')
            ->willReturn($resolvedScopeId);
        $this->scopeResolver->expects($this->once())
            ->method('getScope')
            ->with($scopeId)
            ->willReturn($scope);
        $result = $this->target->resolve($indexName, $scopeId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function resolveDataProviderWithScopeId()
    {
        return [
            [
                'index' => 'index_name',
                'scopeId' => 'scope_id',
                'resolverScopeId' => 'scope_id',
                'expected' => 'index_name_index_scope_id'
            ],
            [
                'index' => 'index_name',
                'scopeId' => 10,
                'resolverScopeId' => 20,
                'expected' => 'index_name_index_20'
            ],
            [
                'index' => 'index_name',
                'scopeId' => 10,
                'resolverScopeId' => null,
                // actually you will get exception here thrown in ScopeResolverInterface
                'expected' => 'index_name_index_'
            ],
            [
                'index' => 'some_index',
                'scopeId' => '1235asdf',
                'resolverScopeId' => 'asdf1235',
                'expected' => 'some_index_index_asdf1235'
            ]
        ];
    }
}
