<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Console\Command\PriceIndexerDimensionsModeSetCommand;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;

/**
 * Class PriceIndexerDimensionsModeSetCommand
 * @package Magento\Setup\Console\Command
 */
class PriceIndexerDimensionsModeSetCommandTest extends \Magento\TestFramework\Indexer\TestCase
{
    /** @var  ObjectManagerInterface */
    private $objectManager;

    /** @var  GenerateFixturesCommand */
    private $command;

    /** @var  CommandTester */
    private $commandTester;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->objectManager->get(\Magento\TestFramework\App\Config::class)->clean();

        $this->command = $this->objectManager->create(
            \Magento\Catalog\Console\Command\PriceIndexerDimensionsModeSetCommand::class
        );

        $this->commandTester = new CommandTester($this->command);

        parent::setUp();
    }

    /**
     * tearDown
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * setUpBeforeClass
     */
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

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     *
     * @param $previousMode
     * @param $currentMode
     * @dataProvider modesDataProvider
     */
    public function testSwitchMode($previousMode, $currentMode)
    {
        $this->commandTester->execute(
            [
                PriceIndexerDimensionsModeSetCommand::INPUT_KEY_MODE => $currentMode
            ]
        );
        $expectedOutput = 'Dimensions mode for indexer Product Price was changed from \''
            . $previousMode . '\' to \'' . $currentMode . '\'';

        $actualOutput = $this->commandTester->getDisplay();

        $this->assertContains($expectedOutput, $actualOutput);

        static::assertEquals(
            Cli::RETURN_SUCCESS,
            $this->commandTester->getStatusCode(),
            $this->commandTester->getDisplay(true)
        );
    }

    public function modesDataProvider()
    {
        return [
            [DimensionModeConfiguration::DIMENSION_NONE, DimensionModeConfiguration::DIMENSION_WEBSITE],
            [DimensionModeConfiguration::DIMENSION_WEBSITE, DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP],
            [
                DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP,
                DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP
            ],
            [
                DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP,
                DimensionModeConfiguration::DIMENSION_NONE
            ],
            [
                DimensionModeConfiguration::DIMENSION_NONE,
                DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP
            ],
            [
                DimensionModeConfiguration::DIMENSION_WEBSITE_AND_CUSTOMER_GROUP,
                DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP
            ],
            [DimensionModeConfiguration::DIMENSION_CUSTOMER_GROUP, DimensionModeConfiguration::DIMENSION_WEBSITE],
            [DimensionModeConfiguration::DIMENSION_WEBSITE, DimensionModeConfiguration::DIMENSION_NONE],
        ];
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     */
    public function testSwitchModeForSameMode()
    {
        $this->commandTester->execute(
            [
                PriceIndexerDimensionsModeSetCommand::INPUT_KEY_MODE => DimensionModeConfiguration::DIMENSION_NONE
            ]
        );
        $expectedOutput = 'Dimensions mode for indexer Product Price has not been changed';

        $actualOutput = $this->commandTester->getDisplay();

        $this->assertContains($expectedOutput, $actualOutput);

        static::assertEquals(
            Cli::RETURN_SUCCESS,
            $this->commandTester->getStatusCode(),
            $this->commandTester->getDisplay(true)
        );
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSwitchModeWithInvalidArgument()
    {
        $this->commandTester->execute(
            [
                PriceIndexerDimensionsModeSetCommand::INPUT_KEY_MODE => DimensionModeConfiguration::DIMENSION_NONE .
                    '_not_valid'
            ]
        );
    }
}
