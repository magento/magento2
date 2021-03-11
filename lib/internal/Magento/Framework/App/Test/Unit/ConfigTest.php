<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\ScopeInterface;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeCodeResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeCodeResolver;

    /**
     * @var ConfigTypeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configType;

    /**
     * @var ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
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
    public function getValueDataProvider()
    {
        return [
            ['store', 1],
            ['website'],
        ];
    }
}
