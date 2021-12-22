<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Tests for \Magento\Indexer\Console\Command\IndexerReindexCommand.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexerReindexCommandTest extends TestCase
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
     * @var IndexerReindexCommand
     */
    private $command;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->inputMock = $this->getMockBuilder(InputInterface::class)->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)->getMockForAbstractClass();

        $this->command = $this->objectManager->get(IndexerReindexCommand::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store_group_with_second_website.php
     * @return void
     */
    public function testReindexAll(): void
    {
        $status = $this->command->run($this->inputMock, $this->outputMock);
        $this->assertEquals(Cli::RETURN_SUCCESS, $status, 'Index wasn\'t success');
    }

    /**
     * Check that 'indexer:reindex' command return right code.
     *
     * @magentoDataFixture Magento/Indexer/_files/wrong_config_data.php
     * @return void
     */
    public function testReindexAllWhenSomethingIsWrong(): void
    {
        $status = $this->command->run($this->inputMock, $this->outputMock);
        $this->assertEquals(Cli::RETURN_FAILURE, $status, 'Index didn\'t return failure code');
    }
}
