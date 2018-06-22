<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

class ScopeResolverPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGet()
    {
        $scope = $this->createMock(\Magento\Framework\App\ScopeResolverInterface::class);
        $scopeResolver = $this->_helper->getObject(
            \Magento\Framework\App\ScopeResolverPool::class,
            [
                'scopeResolvers' => ['test' => $scope]
            ]
        );
        $this->assertSame($scope, $scopeResolver->get('test'));
    }

    /**
     * @param string $scope
     *
     * @covers \Magento\Framework\App\ScopeResolverPool::get()
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid scope type
     * @dataProvider testGetExceptionDataProvider
     */
    public function testGetException($scope)
    {
        $scopeResolver = $this->_helper->getObject(
            \Magento\Framework\App\ScopeResolverPool::class,
            [
                'scopeResolvers' => ['test' => new \Magento\Framework\DataObject()]
            ]
        );
        $scopeResolver->get($scope);
    }

    /**
     * @return array
     */
    public function testGetExceptionDataProvider()
    {
        return [
            ['undefined'],
            ['test'],
        ];
    }
}
