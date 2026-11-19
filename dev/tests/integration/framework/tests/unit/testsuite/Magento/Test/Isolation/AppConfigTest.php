<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

/**
 * Test class for \Magento\TestFramework\Isolation\WorkingDirectory.
 */
namespace Magento\Test\Isolation;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\ObjectManager;

class AppConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Isolation\WorkingDirectory
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new \Magento\TestFramework\Isolation\AppConfig();
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    public function testStartTestEndTest()
    {
        $test = $this->createStub(\PHPUnit\Framework\TestCase::class);
        $testAppConfigMock = $this->getMockBuilder(\Magento\TestFramework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->injectTestAppConfig($this->model, $testAppConfigMock);
        $testAppConfigMock->expects($this->once())
            ->method('clean');
        $this->model->startTest($test);
    }

    public function testStartTestReappliesGlobalConfigAfterClean()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $configFile = tempnam(sys_get_temp_dir(), 'global-config');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        file_put_contents($configFile, '<?php return [\'test/test/test\' => \'value\'];');

        $invocationOrder = [];

        $testAppConfigMock = $this->getMockBuilder(\Magento\TestFramework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testAppConfigMock->expects($this->once())
            ->method('clean')
            ->willReturnCallback(function () use (&$invocationOrder) {
                $invocationOrder[] = 'clean';
                return null;
            });

        $mutableScopeConfigMock = $this->createMock(MutableScopeConfigInterface::class);
        $mutableScopeConfigMock->expects($this->once())
            ->method('setValue')
            ->with('test/test/test', 'value', ScopeInterface::SCOPE_STORE)
            ->willReturnCallback(function () use (&$invocationOrder) {
                $invocationOrder[] = 'setValue';
                return null;
            });

        $model = new \Magento\TestFramework\Isolation\AppConfig(
            $configFile,
            new \Magento\TestFramework\Config($mutableScopeConfigMock, $configFile)
        );
        $this->injectTestAppConfig($model, $testAppConfigMock);

        $test = $this->createStub(\PHPUnit\Framework\TestCase::class);
        $model->startTest($test);

        $this->assertSame(['clean', 'setValue'], $invocationOrder);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        unlink($configFile);
    }

    public function testStartTestSkipsGlobalConfigWhenFileIsMissing()
    {
        $globalConfigMock = $this->createMock(\Magento\TestFramework\Config::class);
        $globalConfigMock->expects($this->never())->method('rewriteAdditionalConfig');

        $testAppConfigMock = $this->getMockBuilder(\Magento\TestFramework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testAppConfigMock->expects($this->once())->method('clean');

        $model = new \Magento\TestFramework\Isolation\AppConfig('/non/existing/global-config.php', $globalConfigMock);
        $this->injectTestAppConfig($model, $testAppConfigMock);

        $test = $this->createStub(\PHPUnit\Framework\TestCase::class);
        $model->startTest($test);
    }

    public function testStartTestWithoutGlobalConfigFile()
    {
        $testAppConfigMock = $this->getMockBuilder(\Magento\TestFramework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testAppConfigMock->expects($this->once())->method('clean');
        $this->injectTestAppConfig($this->model, $testAppConfigMock);

        $test = $this->createStub(\PHPUnit\Framework\TestCase::class);
        $this->model->startTest($test);
    }

    /**
     * Inject Test App Config mock into the model
     *
     * @param \Magento\TestFramework\Isolation\AppConfig $model
     * @param \Magento\TestFramework\App\Config $testAppConfig
     * @return void
     */
    private function injectTestAppConfig($model, $testAppConfig)
    {
        $modelReflection = new \ReflectionClass($model);
        $testAppConfigProperty = $modelReflection->getProperty('testAppConfig');
        $testAppConfigProperty->setValue($model, $testAppConfig);
    }
}
