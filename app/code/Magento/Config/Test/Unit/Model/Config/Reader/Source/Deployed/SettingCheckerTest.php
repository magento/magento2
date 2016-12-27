<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Reader\Source\Deployed;

use Magento\Config\Model\Config\Reader;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Config\Model\Placeholder\PlaceholderFactory;

/**
 * Test class for checking settings that defined in config file
 */
class SettingCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var PlaceholderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeholderMock;

    /**
     * @var Config\ScopeCodeResolver|\PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
    {
        $this->configMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->placeholderMock = $this->getMockBuilder(PlaceholderInterface::class)
            ->getMockForAbstractClass();
        $this->scopeCodeResolverMock = $this->getMockBuilder(Config\ScopeCodeResolver::class)
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
     * @param bool $expectedResult
     * @dataProvider isReadonlyDataProvider
     */
    public function testIsReadonly($path, $scope, $scopeCode, $confValue, array $variables, $expectedResult)
    {
        $this->placeholderMock->expects($this->once())
            ->method('isApplicable')
            ->willReturn(true);
        $this->placeholderMock->expects($this->once())
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
            ->willReturnMap([
                [
                    'system/' . $scope . "/" . ($scopeCode ? $scopeCode . '/' : '') . $path,
                    null,
                    $confValue
                ],
            ]);

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
                'expectedResult' => true,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => null,
                'variables' => ['SOME_PLACEHOLDER' => 'value'],
                'expectedResult' => true,
            ],
            [
                'path' => 'general/web/locale',
                'scope' => 'website',
                'scopeCode' => 'myWebsite',
                'confValue' => null,
                'variables' => [],
                'expectedResult' => false,
            ]
        ];
    }

    protected function tearDown()
    {
        $_ENV = $this->env;
    }
}
