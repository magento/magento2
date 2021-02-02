<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Test\Integration;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillCompareInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillReadInterface;
use Magento\Framework\Shell;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class PoisonPillAppyAfterCommandRunTest extends TestCase
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
     * @var Shell
     */
    private $shell;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->poisonPillRead = $objectManager->get(PoisonPillReadInterface::class);
        $this->poisonPillCompare = $objectManager->get(PoisonPillCompareInterface::class);
        $this->shell = $objectManager->get(Shell::class);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @covers \Magento\MessageQueue\Setup\Recurring
     *
     * @magentoDbIsolation disabled
     */
    public function testChangeVersion()
    {
        $version = $this->poisonPillRead->getLatestVersion();
        $this->shell->execute(PHP_BINARY . ' -f %s queue:consumers:restart', [BP . '/bin/magento']);
        $this->assertEquals(false, $this->poisonPillCompare->isLatestVersion($version));
    }
}
