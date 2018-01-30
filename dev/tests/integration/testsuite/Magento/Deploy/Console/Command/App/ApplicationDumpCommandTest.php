<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationDumpCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DeploymentConfig\Reader
     */
    private $reader;

    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->reader = $this->objectManager->get(DeploymentConfig\Reader::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Deploy/_files/config_data.php
     */
    public function testExecute()
    {
        $this->objectManager->configure([
            \Magento\Config\Model\Config\Export\ExcludeList::class => [
                'arguments' => [
                    'configs' => [
                        'web/test/test_value_1' => '',
                        'web/test/test_value_2' => '',
                        'web/test/test_sensitive' => '1',
                    ],
                ],
            ],
        ]);

        $comment = 'The configuration file doesn\'t contain the sensitive data by security reason. '
            . 'The sensitive data can be stored in the next environment variables:'
            . "\nCONFIG__DEFAULT__WEB__TEST__TEST_SENSITIVE for web/test/test_sensitive";
        $outputMock = $this->getMock(OutputInterface::class);
        $outputMock->expects($this->at(0))
            ->method('writeln')
            ->with(['system' => $comment]);
        $outputMock->expects($this->at(1))
            ->method('writeln')
            ->with('<info>Done.</info>');

        /** @var ApplicationDumpCommand command */
        $command = $this->objectManager->create(ApplicationDumpCommand::class);
        $command->run($this->getMock(InputInterface::class), $outputMock);

        $config = $this->reader->loadConfigFile(ConfigFilePool::APP_CONFIG, $this->getFileName());

        $this->assertArrayHasKey(
            'test_value_1',
            $config['system']['default']['web']['test']
        );
        $this->assertArrayHasKey(
            'test_value_2',
            $config['system']['default']['web']['test']
        );
        $this->assertArrayNotHasKey(
            'test_sensitive',
            $config['system']['default']['web']['test']
        );
    }

    public function tearDown()
    {
        $file = $this->getFileName();
        /** @var DirectoryList $dirList */
        $dirList = $this->objectManager->get(DirectoryList::class);
        $path = $dirList->getPath(DirectoryList::CONFIG);
        $driverPool = $this->objectManager->get(DriverPool::class);
        $fileDriver = $driverPool->getDriver(DriverPool::FILE);
        if ($fileDriver->isExists($path . '/' . $file)) {
            unlink($path . '/' . $file);
        }
        /** @var DeploymentConfig $deploymentConfig */
        $deploymentConfig = $this->objectManager->get(DeploymentConfig::class);
        $deploymentConfig->resetData();
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
}
