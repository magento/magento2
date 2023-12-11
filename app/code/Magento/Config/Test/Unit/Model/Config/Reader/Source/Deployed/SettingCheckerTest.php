<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for checking settings that defined in config file
 */
class SettingCheckerTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var PlaceholderInterface|MockObject
     */
    private $placeholderMock;

    /**
     * @var Config\ScopeCodeResolver|MockObject
     */
    private $scopeCodeResolverMock;

    /**
     * @var SettingChecker
     */
    private $checker;

    /**
     * @var array
     */
    private $env;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->placeholderMock = $this->getMockBuilder(PlaceholderInterface::class)
            ->getMockForAbstractClass();
        $this->scopeCodeResolverMock = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $placeholderFactoryMock = $this->getMockBuilder(PlaceholderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->env = $_ENV;

        $placeholderFactoryMock->expects($this->once())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->checker = new SettingChecker($this->configMock, $placeholderFactoryMock, $this->scopeCodeResolverMock);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param string $scopeCode
     * @param string|null $confValue
     * @param array $variables
     * @param array $configMap
     * @param bool $expectedResult
     * @dataProvider isReadonlyDataProvider
     */
    public function testIsReadonly(
        $path,
        $scope,
        $scopeCode,
        $confValue,
        array $variables,
        array $configMap,
        $expectedResult
    ) {
        $this->placeholderMock->expects($this->any())
            ->method('isApplicable')
            ->willReturn(true);
        $this->placeholderMock->expects($this->any())
            ->method('generate')
            ->with($path, $scope, $scopeCode)
            ->willReturn('SOME_PLACEHOLDER');
        $this->scopeCodeResolverMock->expects($this->any())
            ->method('resolve')
            ->willReturnMap(
                [
                    ['website', 'myWebsite', ($scopeCode ? $scopeCode : '')]
                ]
            );

        $_ENV = array_merge($this->env, $variables);

        $this->configMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                array_merge(
                    [
                        [
                            'system/' . $scope . "/" . ($scopeCode ? $scopeCode . '/' : '') . $path,
                            null,
                            $confValue
                        ],
                    ],
                    $configMap
                )
            );

        $this->assertSame($expectedResult, $this->checker->isReadOnly($path, $scope, $scopeCode));
    }

    /**
     * @return array
     */
    public function isReadonlyDataProvider()
    {
        return [
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => 'value',
                'variables' => [],
                'configMap' => [],
                'expectedResult' => true,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => null,
                'variables' => ['SOME_PLACEHOLDER' => 'value'],
                'configMap' => [],
                'expectedResult' => true,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => null,
                'variables' => [],
                'configMap' => [],
                'expectedResult' => false,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => null,
                'variables' => [],
                'configMap' => [
                    [
                        'system/default/general/web/locale',
                        null,
                        'default_value',
                    ],
                ],
                'expectedResult' => true,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => null,
                'variables' => [],
                'configMap' => [
                    [
                        'system/default/general/web/locale',
                        null,
                        'default_value',
                    ],
                ],
                'expectedResult' => true,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'store',
                'scopeCode' => 'myStore',
                'confValue' => null,
                'variables' => [],
                'configMap' => [
                    [
                        'system/default/general/web/locale',
                        null,
                        'default_value',
                    ],
                    [
                        'system/website/myWebsite/general/web/locale',
                        null,
                        null,
                    ],
                ],
                'expectedResult' => true,
            ]
        ];
    }

    protected function tearDown(): void
    {
        $_ENV = $this->env;
    }
}
