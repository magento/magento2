<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ScopeResolverPoolTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_helper = new ObjectManager($this);
    }

    public function testGet()
    {
        $scope = $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $scopeResolver = $this->_helper->getObject(
            ScopeResolverPool::class,
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
     * @dataProvider testGetExceptionDataProvider
     */
    public function testGetException($scope)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid scope type');
        $scopeResolver = $this->_helper->getObject(
            ScopeResolverPool::class,
            [
                'scopeResolvers' => ['test' => new DataObject()]
            ]
        );
        $scopeResolver->get($scope);
    }

    /**
     * @return array
     */
    public static function testGetExceptionDataProvider()
    {
        return [
            ['undefined'],
            ['test'],
        ];
    }
}
