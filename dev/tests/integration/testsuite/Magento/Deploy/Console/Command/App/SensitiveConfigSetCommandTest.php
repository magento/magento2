<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\InteractiveCollector;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\Reader;
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
class SensitiveConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

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
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(Reader::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->config = $this->loadConfig();
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->getFileName(),
            file_get_contents(__DIR__ . '/../../../_files/_config.local.php')
        );
    }

    /**
     * @param $scope
     * @param $scopeCode
     * @param callable $assertCallback
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDbIsolation enabled
     * @dataProvider testExecuteDataProvider
     */
    public function testExecute($scope, $scopeCode, callable $assertCallback)
    {
        $outputMock = $this->getMock(OutputInterface::class);
        $outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>Configuration value saved in app/etc/config.php</info>');

        $inputMock = $this->getMock(InputInterface::class);
        $inputMock->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive(
                [SensitiveConfigSetCommand::INPUT_ARGUMENT_PATH],
                [SensitiveConfigSetCommand::INPUT_ARGUMENT_VALUE]
            )
            ->willReturnOnConsecutiveCalls(
                'some/config/path_two',
                'sensitiveValue'
            );
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
                null
            );

        /** @var SensitiveConfigSetCommand command */
        $command = $this->objectManager->create(SensitiveConfigSetCommand::class);
        $command->run($inputMock, $outputMock);

        $config = $this->loadConfig();

        $assertCallback($config);
    }

    public function testExecuteDataProvider()
    {
        return [
            [
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                function (array $config) {
                    $this->assertTrue(isset($config['system']['default']['some']['config']['path_two']));
                    $this->assertEquals(
                        'sensitiveValue',
                        $config['system']['default']['some']['config']['path_two']
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
     * @dataProvider testExecuteInteractiveDataProvider
     */
    public function testExecuteInteractive($scope, $scopeCode, callable $assertCallback)
    {
        $inputMock = $this->getMock(InputInterface::class);
        $outputMock = $this->getMock(OutputInterface::class);
        $outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>Please set configuration values or skip them by pressing [Enter]:</info>');
        $outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>Configuration values saved in app/etc/config.php</info>');
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
                true
            );

        $questionHelperMock = $this->getMock(QuestionHelper::class);
        $questionHelperMock->expects($this->exactly(3))
            ->method('ask')
            ->willReturn('sensitiveValue');

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
                'collectorFactory' => $collectorFactoryMock
            ]
        );
        $command->run($inputMock, $outputMock);

        $config = $this->loadConfig();

        $assertCallback($config);
    }

    public function testExecuteInteractiveDataProvider()
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
    public function tearDown()
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->delete(
            $this->getFileName()
        );
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        /** @var ConfigFilePool $configFilePool */
        $configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $filePool = $configFilePool->getInitialFilePools();

        return $filePool[ConfigFilePool::LOCAL][ConfigFilePool::APP_CONFIG];
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
}
