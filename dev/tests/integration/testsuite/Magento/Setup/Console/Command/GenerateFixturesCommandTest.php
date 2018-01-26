<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateFixturesCommandCommandTest
 * @package Magento\Setup\Console\Command
 */
class GenerateFixturesCommandTest extends \PHPUnit\Framework\TestCase
{
    private $fixtureModelMock;
    private $objectManager;
    /**
     * @var GenerateFixturesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->setMethods(['getObjectManager'])
            ->setConstructorArgs([$this->objectManager->get(IndexerReindexCommand::class)])
            ->getMock();
        $this->fixtureModelMock
            ->method('getObjectManager')
            ->willReturn($this->objectManager);

        $this->command = $this->objectManager->create(
            GenerateFixturesCommand::class,
            [
                'fixtureModel' => $this->fixtureModelMock
            ]
        );

        $this->setIncrement(3);

        $this->commandTester = new CommandTester($this->command);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->setIncrement(1);

        parent::tearDown();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testExecute()
    {
        $this->commandTester->execute(
            [GenerateFixturesCommand::PROFILE_ARGUMENT => BP . '/setup/performance-toolkit/profiles/ce/small.xml']
        );

        static::assertEquals(Cli::RETURN_SUCCESS, $this->commandTester->getStatusCode(), $this->commandTester->getDisplay());
    }

    /**
     * @param $value
     */
    private function setIncrement($value)
    {
        $db = Bootstrap::getInstance()->getBootstrap()->getApplication()->getDbInstance();
        /** @var \Magento\TestFramework\Db\Adapter\Mysql $connection */
        $connection = Bootstrap::getObjectManager()->create(
            \Magento\TestFramework\Db\Adapter\Mysql::class,
            [
                'config' => [
                    'type' => 'pdo_mysql',
                    'host' => $db->getHost(),
                    'username' => $db->getUser(),
                    'password' => $db->getPassword(),
                    'dbname' => $db->getSchema(),
                    'active' => true,
                ]
            ]
        );
        $connection->query("SET GLOBAL auto_increment_increment=$value");
        $connection->query("SET GLOBAL auto_increment_offset=$value");
    }
}
