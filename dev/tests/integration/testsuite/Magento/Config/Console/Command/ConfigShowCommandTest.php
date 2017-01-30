<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Store\Model\ScopeInterface;
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
    * @var CommandTester
    */
    private $commandTester;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $command = $this->objectManager->create(ConfigShowCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @param array $configs
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Config/_files/config_data.php
     * @dataProvider executeDataProvider
     */
    public function testExecute($scope, $scopeCode, array $configs)
    {
        foreach ($configs as $inputPath => $configValue) {
            $arguments = [
                ConfigShowCommand::INPUT_ARGUMENT_PATH => $inputPath
            ];

            if ($scope !== null) {
                $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE] = $scope;
            }
            if ($scopeCode !== null) {
                $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE_CODE] = $scopeCode;
            }

            $this->commandTester->execute($arguments);

            $this->assertEquals(
                Cli::RETURN_SUCCESS,
                $this->commandTester->getStatusCode()
            );
            $this->assertContains(
                $configValue,
                $this->commandTester->getDisplay()
            );
        }
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @param array $configs
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Config/_files/config_data.php
     * @dataProvider executeDataProvider
     */
    public function testExecuteConfigGroup($scope, $scopeCode, array $configs)
    {
        $arguments = [
            ConfigShowCommand::INPUT_ARGUMENT_PATH => 'web/test'
        ];

        if ($scope !== null) {
            $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE] = $scope;
        }
        if ($scopeCode !== null) {
            $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE_CODE] = $scopeCode;
        }

        $this->commandTester->execute($arguments);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $this->commandTester->getStatusCode()
        );

        foreach ($configs as $configPath => $configValue) {
            $this->assertContains(
                sprintf("%s - %s", $configPath, $configValue),
                $this->commandTester->getDisplay()
            );
        }
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                [
                    'web/test/test_value_1' => 'http://default.test/',
                    'web/test/test_value_2' => 'someValue',
                    'web/test/test_value_3' => '100',
                ]
            ],
            [
                ScopeInterface::SCOPE_WEBSITES,
                'base',
                [
                    'web/test/test_value_1' => 'http://website.test/',
                    'web/test/test_value_2' => 'someWebsiteValue',
                    'web/test/test_value_3' => '101',
                ]
            ]
        ];
    }
}
