<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

class ScopeResolverPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGet()
    {
        $scope = $this->getMock('\Magento\Framework\App\ScopeResolverInterface');
        $scopeResolver = $this->_helper->getObject('Magento\Framework\App\ScopeResolverPool', [
            'scopeResolvers' => [
                'test' => $scope,
            ]
        ]);
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
        $scopeResolver = $this->_helper->getObject('Magento\Framework\App\ScopeResolverPool', [
            'scopeResolvers' => [
                'test' => new \Magento\Framework\Object(),
            ]
        ]);
        $scopeResolver->get($scope);
    }

    public function testGetExceptionDataProvider()
    {
        return [
            ['undefined'],
            ['test'],
        ];
    }
}
