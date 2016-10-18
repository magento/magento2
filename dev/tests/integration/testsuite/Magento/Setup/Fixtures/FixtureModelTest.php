<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class Application test
 */
class FixtureModelTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * Profile generator working directory
     *
     * @var string
     */
    protected static $_generatorWorkingDir;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public static function setUpBeforeClass()
    {
        $db = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        self::$_generatorWorkingDir = realpath(__DIR__ . '/../../../../../../../setup/src/Magento/Setup/Fixtures');
        copy(
            self::$_generatorWorkingDir . '/tax_rates.csv',
            self::$_generatorWorkingDir . '/tax_rates.csv.bak'
        );
        copy(
            __DIR__ . '/_files/tax_rates.csv',
            self::$_generatorWorkingDir . '/tax_rates.csv'
        );
        parent::setUpBeforeClass();
    }

    public function testTest()
    {
        $reindexCommand = Bootstrap::getObjectManager()->get(
            \Magento\Indexer\Console\Command\IndexerReindexCommand::class
        );
        $parser = Bootstrap::getObjectManager()->get(\Magento\Framework\Xml\Parser::class);
        $itfApplication = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()->getApplication();
        $model = new FixtureModel($reindexCommand, $parser, $itfApplication->getInitParams());
        $model->loadConfig(__DIR__ . '/_files/small.xml');
        $model->initObjectManager();

        foreach ($model->loadFixtures()->getFixtures() as $fixture) {
            $fixture->execute();
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        unlink(self::$_generatorWorkingDir . '/tax_rates.csv');
        rename(
            self::$_generatorWorkingDir . '/tax_rates.csv.bak',
            self::$_generatorWorkingDir . '/tax_rates.csv'
        );
        /** @var $appCache \Magento\Framework\App\Cache */
        $appCache = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache::class);
        $appCache->clean(
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
            ]
        );
    }
}
