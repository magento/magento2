<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSet\DefaultProcessor;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * Test for DefaultProcessor.
 *
 * @see DefaultProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultProcessorTest extends TestCase
{
    /**
     * @var DefaultProcessor
     */
    private $model;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    /**
     * @var ConfigPathResolver|Mock
     */
    private $configPathResolverMock;

    /**
     * @var PreparedValueFactory|Mock
     */
    private $preparedValueFactoryMock;

    /**
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @var AbstractDb|Mock
     */
    private $resourceModelMock;

    /**
     * @var ConfigFactory|Mock
     */
    private $configFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->configPathResolverMock = $this->createMock(ConfigPathResolver::class);
        $this->resourceModelMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMockForAbstractClass();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResource'])
            ->getMock();
        $this->preparedValueFactoryMock = $this->createMock(PreparedValueFactory::class);
        $this->configFactory = $this->createMock(ConfigFactory::class);

        $this->model = new DefaultProcessor(
            $this->preparedValueFactoryMock,
            $this->deploymentConfigMock,
            $this->configPathResolverMock,
            $this->configFactory
        );
    }

    /**
     * Tests process of default flow.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string|null $scopeCode
     * @dataProvider processDataProvider
     */
    public function testProcess($path, $value, $scope, $scopeCode)
    {
        $this->configMockForProcessTest($path, $scope, $scopeCode);

        $config = $this->createMock(Config::class);
        $this->configFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['scope' => $scope, 'scope_code' => $scopeCode]])
            ->willReturn($config);
        $config->expects($this->once())
            ->method('setDataByPath')
            ->with($path, $value);
        $config->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->model->process($path, $value, $scope, $scopeCode);
    }

    /**
     * @return array
     */
    public static function processDataProvider()
    {
        return [
            ['test/test/test', 'value', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null],
            ['test/test/test', 'value', ScopeInterface::SCOPE_WEBSITE, 'base'],
            ['test/test/test', 'value', ScopeInterface::SCOPE_STORE, 'test'],
        ];
    }

    /**
     * @param string $path
     * @param string $scope
     * @param string|null $scopeCode
     */
    private function configMockForProcessTest($path, $scope, $scopeCode)
    {
        $this->configPathResolverMock->expects($this->once())
            ->method('resolve')
            ->with($path, $scope, $scopeCode, System::CONFIG_TYPE)
            ->willReturn('system/default/test/test/test');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturnMap([
                ['system/default/test/test/test', null],
            ]);
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testProcessLockedValue()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'The value you set has already been locked. To change the value, use the --lock-env option.'
        );
        $path = 'test/test/test';
        $value = 'value';

        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturnMap([
                ['db', null, 'exists'],
                ['system/default/test/test/test', null, 5],
            ]);
        $this->configPathResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');

        $this->configFactory->expects($this->never())
            ->method('create');

        $this->model->process($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }
}
