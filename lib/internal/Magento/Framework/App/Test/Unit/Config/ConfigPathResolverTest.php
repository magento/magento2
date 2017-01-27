<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ConfigPathResolver;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * {@inheritdoc}
 */
class ConfigPathResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigPathResolver
     */
    private $model;

    /**
     * @var ScopeCodeResolver|Mock
     */
    private $scopeCodeResolverMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->scopeCodeResolverMock = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ConfigPathResolver(
            $this->scopeCodeResolverMock
        );
    }

    /**
     * @param string $path
     * @param string $scope
     * @param string $scopeCode
     * @param string $type
     * @param string $expected
     * @dataProvider resolveDataProvider
     */
    public function testResolve($path, $scope, $scopeCode, $type, $expected)
    {
        $this->scopeCodeResolverMock->expects($this->any())
            ->method('resolve')
            ->willReturn($scopeCode ? $scopeCode : 'test_code');

        $this->assertSame($expected, $this->model->resolve($path, $scope, $scopeCode, $type));
    }

    public function resolveDataProvider()
    {
        return [
            ['/test/test/test/', 'default', null, null, 'default/test/test/test'],
            ['test/test/test', 'default', null, 'system', 'system/default/test/test/test'],
            ['test/test/test', 'website', 'base', 'system', 'system/websites/base/test/test/test'],
            ['test/test/test', 'websites', null, 'system', 'system/websites/test_code/test/test/test'],
        ];
    }
}
