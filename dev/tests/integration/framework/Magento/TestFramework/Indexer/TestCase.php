<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Indexer;

use Symfony\Component\Console\Tester\CommandTester;
use Magento\TestFramework\App\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Console\Command\PriceIndexerDimensionsModeSetCommand as SetModeCommand;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;

class TestCase extends \PHPUnit\Framework\TestCase
{
    private $setModeCommand;
    private $objectManager;

    public static function tearDownAfterClass()
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();
    }

    public static function setUpBeforeClass()
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

    protected function setDimensionMode($mode = DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP)
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->get(Config::class)->clean();

        $command = $this->objectManager->create(SetModeCommand::class);
        $this->setModeCommand = new CommandTester($command);
        $this->setModeCommand->execute([SetModeCommand::INPUT_KEY_MODE => $mode]);

        parent::setUp();
    }
}
