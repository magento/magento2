<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigShowCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ScopeConfigInterface
     */
    private $appConfig;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->appConfig = $this->objectManager->get(ScopeConfigInterface::class);

        $command = $this->objectManager->create(ConfigShowCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @param array $configs
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Config/_files/config_data.php
     * @dataProvider testExecuteDataProvider
     */
    public function testExecute($scope, $scopeCode, array $configs)
    {
        foreach ($configs as $configPath => $configValue) {
            $arguments = [
                ConfigShowCommand::INPUT_ARGUMENT_PATH => $configPath
            ];

            if ($scope !== null) {
                $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE] = $scope;
            }
            if ($scopeCode !== null) {
                $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE_CODE] = $scopeCode;
            }

            $this->commandTester->execute($arguments);

            $appConfigValue = $this->appConfig->getValue($configPath, $scope, $scopeCode);
            $this->assertEquals(
                Cli::RETURN_SUCCESS,
                $this->commandTester->getStatusCode()
            );
            $this->assertEquals(
                $configValue,
                $appConfigValue
            );
            $this->assertContains(
                $appConfigValue,
                $this->commandTester->getDisplay()
            );
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Config/_files/config_data.php
     */
    public function testExecuteConfigGroup()
    {
        $this->commandTester->execute([
            ConfigShowCommand::INPUT_ARGUMENT_PATH => 'web/test'
        ]);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $this->commandTester->getStatusCode()
        );
        $this->assertContains(
            'http://default.test/',
            $this->commandTester->getDisplay()
        );
        $this->assertContains(
            'someValue',
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteDataProvider()
    {
        return [
            [
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                [
                    'web/test/test_value_1' => 'http://default.test/',
                    'web/test/test_value_2' => 'someValue',
                    'web/test/test_value_3' => 100,
                ]
            ],
            [
                'website',
                'base',
                [
                    'web/test/test_value_1' => 'http://website.test/',
                    'web/test/test_value_2' => 'someWebsiteValue',
                    'web/test/test_value_3' => 101,
                ]
            ]
        ];
    }
}
