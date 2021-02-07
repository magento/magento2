<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Integration;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PoisonPillApplyDuringSetupUpgradeTest extends TestCase
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
     * @var UpgradeCommand
     */
    private $upgradeCommand;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->poisonPillRead = $objectManager->get(PoisonPillReadInterface::class);
        $this->poisonPillCompare = $objectManager->get(PoisonPillCompareInterface::class);
        $this->upgradeCommand = $objectManager->get(UpgradeCommand::class);
    }

    /**
     * @covers \Magento\MessageQueue\Setup\Recurring
     *
     * @magentoDbIsolation disabled
     */
    public function testChangeVersion()
    {
        $version = $this->poisonPillRead->getLatestVersion();
        $this->runTestUpgradeCommand();
        $this->assertEquals(false, $this->poisonPillCompare->isLatestVersion($version));
    }

    /**
     * @return void
     */
    private function runTestUpgradeCommand(): void
    {
        $commandTester = new CommandTester($this->upgradeCommand);
        $commandTester->execute(['--keep-generated']);
    }
}
