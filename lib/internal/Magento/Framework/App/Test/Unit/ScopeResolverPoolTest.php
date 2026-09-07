<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $scope = $this->createMock(ScopeResolverInterface::class);
        $scopeResolver = $this->_helper->getObject(
            ScopeResolverPool::class,
            [
                'scopeResolvers' => ['test' => $scope]
            ]
        );
        $this->assertSame($scope, $scopeResolver->get('test'));
    }

    /**     * @covers \Magento\Framework\App\ScopeResolverPool::get()
     */
    #[DataProvider('getExceptionDataProvider')]
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
    public static function getExceptionDataProvider()
    {
        return [
            ['undefined'],
            ['test'],
        ];
    }
}
