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
use Magento\Setup\Fixtures\FixtureModel;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateFixturesCommandCommandTest
 *
 * @magentoDbIsolation disabled
 */
class GenerateFixturesCommandTest extends \Magento\TestFramework\Indexer\TestCase
{
    /** @var  CommandTester */
    private $indexerCommand;

    /** @var  FixtureModel */
    private $fixtureModelMock;

    /** @var  ObjectManagerInterface */
    private $objectManager;

    /** @var  GenerateFixturesCommand */
    private $command;

    /** @var  CommandTester */
    private $commandTester;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->objectManager->get(\Magento\TestFramework\App\Config::class)->clean();

        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->onlyMethods(['getObjectManager'])
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
            ->onlyMethods(['create'])
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

    /**
     * teardown
     */
    protected function tearDown(): void
    {
        $this->setIncrement(1);

        self::restoreFromDb();
        self::$dbRestored = true;

        parent::tearDown();
    }

    public static function setUpBeforeClass(): void
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     */
    public function testExecute()
    {
        $profile = realpath(__DIR__ . "/_files/min_profile.xml");
        $this->commandTester->execute(
            [
                GenerateFixturesCommand::PROFILE_ARGUMENT => $profile,
                '--' . GenerateFixturesCommand::SKIP_REINDEX_OPTION => true
            ]
        );
        $this->indexerCommand->execute([]);

        static::assertEquals(
            Cli::RETURN_SUCCESS,
            $this->indexerCommand->getStatusCode(),
            $this->indexerCommand->getDisplay(true)
        );

        static::assertEquals(
            Cli::RETURN_SUCCESS,
            $this->commandTester->getStatusCode(),
            $this->commandTester->getDisplay(true)
        );
    }

    /**
     * @param $value
     */
    private function setIncrement($value)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $db */
        $db = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $db->query("SET @@session.auto_increment_increment=$value");
        $db->query("SET @@session.auto_increment_offset=$value");
    }
}
