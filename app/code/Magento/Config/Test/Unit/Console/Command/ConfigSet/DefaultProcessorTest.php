<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSet\DefaultProcessor;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for DefaultProcessor.
 *
 * @see DefaultProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultProcessorTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configPathResolverMock = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceModelMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMockForAbstractClass();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResource'])
            ->getMock();
        $this->preparedValueFactoryMock = $this->getMockBuilder(PreparedValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new DefaultProcessor(
            $this->preparedValueFactoryMock,
            $this->deploymentConfigMock,
            $this->configPathResolverMock
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

        $this->preparedValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->valueMock);
        $this->valueMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->resourceModelMock);
        $this->resourceModelMock->expects($this->once())
            ->method('save')
            ->with($this->valueMock)
            ->willReturnSelf();

        $this->model->process($path, $value, $scope, $scopeCode);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['test/test/test', 'value', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null],
            ['test/test/test', 'value', ScopeInterface::SCOPE_WEBSITE, 'base'],
            ['test/test/test', 'value', ScopeInterface::SCOPE_STORE, 'test'],
        ];
    }

    public function testProcessWithWrongValueInstance()
    {
        $path = 'test/test/test';
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeCode = null;
        $value = 'value';
        $valueInterfaceMock = $this->getMockBuilder(ValueInterface::class)
            ->getMockForAbstractClass();

        $this->configMockForProcessTest($path, $scope, $scopeCode);

        $this->preparedValueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($valueInterfaceMock);
        $this->valueMock->expects($this->never())
            ->method('getResource');
        $this->resourceModelMock->expects($this->never())
            ->method('save');

        $this->model->process($path, $value, $scope, $scopeCode);
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The value you set has already been locked. To change the value, use the --lock option.
     */
    public function testProcessLockedValue()
    {
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

        $this->model->process($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }
}
