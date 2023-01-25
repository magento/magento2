<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\InteractiveCollector;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SensitiveConfigSetFacade;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SensitiveConfigSetCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var array
     */
    private $envConfig;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->writer = $this->objectManager->get(Writer::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);

        $this->envConfig = $this->loadEnvConfig();
        $this->config = $this->loadConfig();

        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            file_get_contents(__DIR__ . '/../../../_files/config.php')
        );
    }

    /**
     * @param $scope
     * @param $scopeCode
     * @param callable $assertCallback
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDbIsolation enabled
     * @dataProvider executeDataProvider
     * @return void
     */
    public function testExecute($scope, $scopeCode, callable $assertCallback)
    {
        $outputMock = $this->getMockForAbstractClass(OutputInterface::class);
        $outputMock
            ->method('writeln')
            ->withConsecutive(['<info>Configuration value saved in app/etc/env.php</info>']);

        $inputMocks = [];

        $inputMocks[] = $this->createInputMock(
            'some/config/path_two',
            'sensitiveValue',
            $scope,
            $scopeCode
        );

        $inputMocks[] = $this->createInputMock(
            'some/config/path_three',
            'sensitiveValue',
            $scope,
            $scopeCode
        );

        // attempt to overwrite existing value for path with null (should not be allowed)
        $inputMocks[] = $this->createInputMock(
            'some/config/path_three',
            null,
            $scope,
            $scopeCode
        );

        foreach ($inputMocks as $inputMock) {
            /** @var SensitiveConfigSetCommand command */
            $command = $this->objectManager->create(SensitiveConfigSetCommand::class);
            $command->run($inputMock, $outputMock);
        }

        $config = $this->loadEnvConfig();

        $assertCallback($config);
    }

    public function executeDataProvider()
    {
        return [
            [
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                function (array $config) {
                    $this->assertTrue(isset($config['system']['default']['some']['config']['path_two']));
                    $this->assertTrue(isset($config['system']['default']['some']['config']['path_three']));
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['default']['some']['config']['path_two']
                    );
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['default']['some']['config']['path_three']
                    );
                }
            ],
            [
                'website',
                'test',
                function (array $config) {
                    $this->assertTrue(isset($config['system']['website']['test']['some']['config']['path_two']));
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['website']['test']['some']['config']['path_two']
                    );
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['website']['test']['some']['config']['path_three']
                    );
                }
            ]
        ];
    }

    /**
     * @param $scope
     * @param $scopeCode
     * @param callable $assertCallback
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDbIsolation enabled
     * @dataProvider executeInteractiveDataProvider
     * @return void
     */
    public function testExecuteInteractive($scope, $scopeCode, callable $assertCallback)
    {
        $inputMock = $this->createInputMock(null, null, $scope, $scopeCode);

        $outputMock = $this->getMockForAbstractClass(OutputInterface::class);
        $outputMock
            ->method('writeln')
            ->withConsecutive(
                ['<info>Please set configuration values or skip them by pressing [Enter]:</info>'],
                ['<info>Configuration values saved in app/etc/env.php</info>']
            );
        $command = $this->createInteractiveCommand('sensitiveValue');
        $command->run($inputMock, $outputMock);

        // attempt to overwrite existing value for path with null (should not be allowed)
        $inputMock = $this->createInputMock(null, null, $scope, $scopeCode);
        $command = $this->createInteractiveCommand(null);
        $command->run($inputMock, $outputMock);

        $config = $this->loadEnvConfig();

        $assertCallback($config);
    }

    public function executeInteractiveDataProvider()
    {
        return [
            [
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                function (array $config) {
                    $this->assertTrue(isset($config['system']['default']['some']['config']['path_one']));
                    $this->assertTrue(isset($config['system']['default']['some']['config']['path_two']));
                    $this->assertTrue(isset($config['system']['default']['some']['config']['path_three']));
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['default']['some']['config']['path_one']
                    );
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['default']['some']['config']['path_two']
                    );
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['default']['some']['config']['path_three']
                    );
                }
            ],
            [
                'website',
                'test',
                function (array $config) {
                    $this->assertTrue(isset($config['system']['website']['test']['some']['config']['path_one']));
                    $this->assertTrue(isset($config['system']['website']['test']['some']['config']['path_two']));
                    $this->assertTrue(isset($config['system']['website']['test']['some']['config']['path_three']));
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['website']['test']['some']['config']['path_one']
                    );
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['website']['test']['some']['config']['path_two']
                    );
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['website']['test']['some']['config']['path_three']
                    );
                }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );

        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);

        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
    }

    /**
     * @return array
     */
    private function loadEnvConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_CONFIG);
    }

    /**
     * @param string|null $key
     * @param string|null $val
     * @param string $scope
     * @param string|null $scopeCode
     * @return InputInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createInputMock($key, $val, $scope, $scopeCode)
    {
        $inputMock = $this->getMockForAbstractClass(InputInterface::class);
        $isInteractive = $key === null;

        if (!$isInteractive) {
            $inputMock->expects($this->exactly(2))
                ->method('getArgument')
                ->withConsecutive(
                    [SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH],
                    [SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE]
                )
                ->willReturnOnConsecutiveCalls(
                    $key,
                    $val
                );
        }

        $inputMock->expects($this->exactly(3))
            ->method('getOption')
            ->withConsecutive(
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE],
                [SensitiveConfigSetCommand::INPUT_OPTION_SCOPE_CODE],
                [SensitiveConfigSetCommand::INPUT_OPTION_INTERACTIVE]
            )
            ->willReturnOnConsecutiveCalls(
                $scope,
                $scopeCode,
                $isInteractive
            );

        return $inputMock;
    }

    /**
     * @param string|null $inputValue
     * @return SensitiveConfigSetCommand
     */
    private function createInteractiveCommand($inputValue)
    {
        $questionHelperMock = $this->createMock(QuestionHelper::class);
        $questionHelperMock->expects($this->exactly(3))
            ->method('ask')
            ->willReturn($inputValue);

        $interactiveCollectorMock = $this->objectManager->create(
            InteractiveCollector::class,
            [
                'questionHelper' => $questionHelperMock
            ]
        );
        $collectorFactoryMock = $this->getMockBuilder(CollectorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectorFactoryMock->expects($this->once())
            ->method('create')
            ->with(CollectorFactory::TYPE_INTERACTIVE)
            ->willReturn($interactiveCollectorMock);

        /** @var SensitiveConfigSetCommand command */
        $command = $this->objectManager->create(
            SensitiveConfigSetCommand::class,
            [
                'facade' => $this->objectManager->create(
                    SensitiveConfigSetFacade::class,
                    [
                        'collectorFactory' => $collectorFactoryMock
                    ]
                )
            ]
        );

        return $command;
    }
}
