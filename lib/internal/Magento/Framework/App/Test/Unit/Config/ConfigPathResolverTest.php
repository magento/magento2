<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeCodeResolver;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * {@inheritdoc}
 */
class ConfigPathResolverTest extends TestCase
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
    protected function setUp(): void
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

    /**
     * @return array
     */
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
