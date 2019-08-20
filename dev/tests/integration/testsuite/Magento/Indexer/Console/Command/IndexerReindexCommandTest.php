<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Console\Command;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Tests for \Magento\Indexer\Console\Command\IndexerReindexCommand.
 *
 * @magentoDbIsolation disabled
 */
class IndexerReindexCommandTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        Bootstrap::getInstance()->reinitialize();
        $this->objectManager = Bootstrap::getObjectManager();

        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store_group_with_second_website.php
     */
    public function testReindexAll()
    {
        $command = $this->objectManager->create(IndexerReindexCommand::class);
        $status = $command->run($this->inputMock, $this->outputMock);
        $this->assertEquals(0, $status, 'Index wasn\'t success');
    }
}
