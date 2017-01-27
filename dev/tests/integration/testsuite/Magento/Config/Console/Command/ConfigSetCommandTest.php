<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Tests the different flows of config:set command.
 *
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

    /**
     * @var OutputInterface|Mock
     */
    private $outputMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->reader = $this->objectManager->get(Reader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->arrayManager = $this->objectManager->get(ArrayManager::class);

        // Snapshot of configuration.
        $this->config = $this->loadConfig();

        // Mocks for objects.
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->loadConfigFile(
            ConfigFilePool::APP_CONFIG,
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            true
        );
    }

    /**
     * Tests default (database) flow.
     * Expects to save value and then error on saving duplicate value.
     *
     * @param string $path
     * @param string|int $value
     * @param string $scope
     * @param string $scopeCode
     * @magentoDbIsolation enabled
     * @dataProvider runDataProvider
     */
    public function testRun($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value],
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_LOCK, false],
                [ConfigSetCommand::OPTION_SCOPE, $scope],
                [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode],
            ]);
        $this->outputMock->expects($this->once())
            ->method('writeln')
            ->withConsecutive(
                ['<info>Value was saved.</info>']
            );

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        $status = $command->run($this->inputMock, $this->outputMock);

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame(
            $value,
            $this->scopeConfig->getValue($path, $scope, $scopeCode)
        );
    }

    /**
     * Retrieves variations with path, value, scope and scope code.
     *
     * @return array
     */
    public function runDataProvider()
    {
        return [
            ['test/test/test', 'value'],
            ['test/test/test2', 'value2', ScopeInterface::SCOPE_WEBSITE, 'base']
        ];
    }

    /**
     * Tests lockable flow.
     * Expects to save value and then error on saving duplicate value.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeCode
     * @magentoDbIsolation enabled
     * @dataProvider runLockDataProvider
     */
    public function testRunLock($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_LOCK, true],
                [ConfigSetCommand::OPTION_SCOPE, $scope],
                [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode]
            ]);
        $this->outputMock->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                ['<info>Value was locked.</info>'],
                ['<info>Value was locked.</info>']
            );

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        /** @var ConfigPathResolver $resolver */
        $resolver = $this->objectManager->get(ConfigPathResolver::class);
        $status = $command->run($this->inputMock, $this->outputMock);
        $config = $this->loadConfig();
        $configPath = $resolver->resolve($path, $scope, $scopeCode, 'system');

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
        $this->assertSame($value, $this->arrayManager->get($configPath, $config));

        $status = $command->run($this->inputMock, $this->outputMock);

        $this->assertSame(Cli::RETURN_SUCCESS, $status);
    }

    /**
     * Retrieves variations with path, value, scope and scope code.
     *
     * @return array
     */
    public function runLockDataProvider()
    {
        return $this->runDataProvider();
    }

    /**
     * Test the extended flow.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeCode
     * @magentoDbIsolation enabled
     * @dataProvider runExtendedDataProvider
     */
    public function testRunExtended(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $arguments = [
            [ConfigSetCommand::ARG_PATH, $path],
            [ConfigSetCommand::ARG_VALUE, $value]
        ];
        $options = [
            [ConfigSetCommand::OPTION_SCOPE, $scope],
            [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode]
        ];
        $optionsLock = array_merge($options, [[ConfigSetCommand::OPTION_LOCK, true]]);

        $this->runCommand($arguments, $options, '<info>Value was saved.</info>');
        $this->runCommand($arguments, $options, '<info>Value was saved.</info>');
        $this->runCommand($arguments, $optionsLock, '<info>Value was locked.</info>');
        $this->runCommand($arguments, $optionsLock, '<info>Value was locked.</info>');
    }

    /**
     * Runs pre-configured command.
     *
     * @param array $arguments
     * @param array $options
     * @param string $expectedMessage
     * @param int $expectedCode
     */
    private function runCommand(
        array $arguments,
        array $options,
        $expectedMessage = '',
        $expectedCode = Cli::RETURN_SUCCESS
    ) {
        $input = clone $this->inputMock;
        $output = clone $this->outputMock;

        $input->expects($this->any())
            ->method('getArgument')
            ->willReturnMap($arguments);
        $input->expects($this->any())
            ->method('getOption')
            ->willReturnMap($options);
        $output->expects($this->once())
            ->method('writeln')
            ->with($expectedMessage);

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        $status = $command->run($input, $output);

        $this->assertSame($expectedCode, $status);
    }

    /**
     * Retrieves variations with path, value, scope and scope code.
     *
     * @return array
     */
    public function runExtendedDataProvider()
    {
        return $this->runDataProvider();
    }

    /**
     * Tests different scenarios for scope options.
     *
     * @param \Closure $expectations
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string $scopeCode
     * @magentoDbIsolation enabled
     * @dataProvider getRunScopeValidationDataProvider
     */
    public function testRunScopeValidation(
        \Closure $expectations,
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, $scope],
                [ConfigSetCommand::OPTION_SCOPE_CODE, $scopeCode]
            ]);

        $expectations($this->outputMock);

        /** @var ConfigSetCommand $command */
        $command = $this->objectManager->create(ConfigSetCommand::class);
        $command->run($this->inputMock, $this->outputMock);
    }

    /**
     * Retrieves variations with callback, path, value, scope and scope code.
     *
     * @return array
     */
    public function getRunScopeValidationDataProvider()
    {
        return [
            [
                function (Mock $output) {
                    $output->expects($this->once())
                        ->method('writeln')
                        ->with('<info>Value was saved.</info>');
                },
                'test/test/test',
                'value',
                ScopeInterface::SCOPE_WEBSITE,
                'base'
            ],
            [
                function (Mock $output) {
                    $output->expects($this->once())
                        ->method('writeln')
                        ->with('<error>Enter a scope before proceeding.</error>');
                },
                'test/test/test',
                null,
                null,
                null

            ],
            [
                function (Mock $output) {
                    $output->expects($this->once())
                        ->method('writeln')
                        ->with('<error>Enter a scope code before proceeding.</error>');
                },
                'test/test/test',
                'value',
                ScopeInterface::SCOPE_WEBSITE,
            ],
            [
                function (Mock $output) {
                    $output->expects($this->once())
                        ->method('writeln')
                        ->with('<error>The "invalid_scope_code" value doesn\'t exist. Enter another value.</error>');
                },
                'test/test/test',
                'value',
                ScopeInterface::SCOPE_WEBSITE,
                'invalid_scope_code'
            ]
        ];
    }
}
