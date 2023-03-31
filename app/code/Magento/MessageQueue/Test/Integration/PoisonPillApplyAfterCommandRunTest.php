<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Integration;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\MessageQueue\Console\RestartConsumerCommand;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PoisonPillApplyAfterCommandRunTest extends TestCase
{
    /**
     * @var PoisonPillReadInterface
     */
    private $poisonPillRead;

    /**
     * @var PoisonPillCompareInterface
     */
    private $poisonPillCompare;

    /**
     * @var RestartConsumerCommand
     */
    private $restartConsumerCommand;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->poisonPillRead = $objectManager->get(PoisonPillReadInterface::class);
        $this->poisonPillCompare = $objectManager->get(PoisonPillCompareInterface::class);
        $this->restartConsumerCommand = $objectManager->create(RestartConsumerCommand::class);
    }

    /**
     * @covers \Magento\MessageQueue\Setup\Recurring
     *
     * @magentoDbIsolation enabled
     */
    public function testChangeVersion(): void
    {
        $version = $this->poisonPillRead->getLatestVersion();
        $this->runTestRestartConsumerCommand();
        $this->assertEquals(false, $this->poisonPillCompare->isLatestVersion($version));
    }

    /**
     * @return void
     */
    private function runTestRestartConsumerCommand(): void
    {
        $commandTester = new CommandTester($this->restartConsumerCommand);
        $commandTester->execute([]);
    }
}
