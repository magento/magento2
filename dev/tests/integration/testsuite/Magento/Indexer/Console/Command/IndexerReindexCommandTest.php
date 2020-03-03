<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Tests for \Magento\Indexer\Console\Command\IndexerReindexCommand.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexerReindexCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    /**
     * @var IndexerReindexCommand
     */
    private $command;

    /**
     * @var IndexerCollectionFactory
     */
    private $indexerCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->inputMock = $this->getMockBuilder(InputInterface::class)->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)->getMockForAbstractClass();

        $this->command = $this->objectManager->get(IndexerReindexCommand::class);
        $this->indexerCollectionFactory = $this->objectManager->create(IndexerCollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store_group_with_second_website.php
     */
    public function testReindexAll()
    {
        $status = $this->command->run($this->inputMock, $this->outputMock);
        $this->assertEquals(
            \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            $status,
            'Index wasn\'t success'
        );

        $notValidIndexers = [];
        $indexers = $this->indexerCollectionFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            if ($indexer->isValid()) {
                continue;
            }

            $notValidIndexers[] = $indexer->getId();
        }
        $this->assertEmpty(
            $notValidIndexers,
            'Following indexers are not valid: ' . implode(', ', $notValidIndexers)
        );
    }
}
