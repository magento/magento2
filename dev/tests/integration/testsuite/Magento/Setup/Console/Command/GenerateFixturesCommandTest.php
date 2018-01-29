<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateFixturesCommandCommandTest
 * @package Magento\Setup\Console\Command
 */
class GenerateFixturesCommandTest extends \PHPUnit\Framework\TestCase
{
    private $indexerCommand;

    private $fixtureModelMock;

    /** @var  ObjectManagerInterface */
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

        $objectFactoryMock = $this->getMockBuilder(ObjectManagerFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectFactoryMock
            ->method('create')
            ->willReturn($this->objectManager);

        $this->indexerCommand = new CommandTester($this->objectManager->create(
            IndexerReindexCommand::class,
            ['objectManagerFactory' => $objectFactoryMock]
        ));

        $this->commandTester = new CommandTester($this->command);

        $this->setIncrement(3);

        parent::setUp();
    }

    public function tearDown()
    {
        $this->setIncrement(1);

        parent::tearDown();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        $this->commandTester->execute(
            [
                GenerateFixturesCommand::PROFILE_ARGUMENT => BP . '/setup/performance-toolkit/profiles/ce/small.xml',
                '--' . GenerateFixturesCommand::SKIP_REINDEX_OPTION => true
            ]
        );
        $this->indexerCommand->execute([]);

        static::assertEquals(
            Cli::RETURN_SUCCESS,
            $this->indexerCommand->getStatusCode(),
            $this->indexerCommand->getDisplay(true)
        );

        static::assertEquals(Cli::RETURN_SUCCESS,
            $this->commandTester->getStatusCode(),
            $this->commandTester->getDisplay(true)
        );
    }

    /**
     * @param $value
     */
    private function setIncrement($value)
    {
        /** @var ResourceConnection $connection */
        $connection = Bootstrap::getObjectManager()->get(
            ResourceConnection::class
        );
        $db = $connection->getConnection();
        $db->query("SET @@session.auto_increment_increment=$value");
        $db->query("SET @@session.auto_increment_offset=$value");
    }
}
