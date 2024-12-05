<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var ScopeCodeResolver|MockObject
     */
    private $scopeCodeResolver;

    /**
     * @var ConfigTypeInterface|MockObject
     */
    private $configType;

    /**
     * @var ScopeInterface|MockObject
     */
    private $scope;

    /**
     * @var Config
     */
    private $appConfig;

    protected function setUp(): void
    {
        $this->scopeCodeResolver = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configType = $this->getMockBuilder(ConfigTypeInterface::class)
            ->getMockForAbstractClass();
        $this->scope = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();

        $this->appConfig = new Config($this->scopeCodeResolver, ['system' => $this->configType]);
    }

    /**
     * @param string $scope
     * @param string|null $scopeCode
     *
     * @dataProvider getValueDataProvider
     * @return void
     */
    public function testGetValue($scope, $scopeCode = null)
    {
        $path = 'path';
        if (!is_string($scope)) {
            $this->scopeCodeResolver->expects($this->once())
                ->method('resolve')
                ->with('stores', $scopeCode)
                ->willReturn('myStore');
        } elseif (!$scopeCode) {
            $this->scope->expects($this->once())
                ->method('getCode')
                ->willReturn('myWebsite');
        }
        $this->configType->expects($this->once())
            ->method('get')
            ->with($scope =='store' ? 'stores/path' : 'websites/myWebsite/path')
            ->willReturn(true);

        $this->assertTrue($this->appConfig->getValue($path, $scope, $scopeCode ?: $this->scope));
    }

    /**
     * @return array
     */
    public static function getValueDataProvider()
    {
        return [
            ['store', 1],
            ['website'],
        ];
    }
}
