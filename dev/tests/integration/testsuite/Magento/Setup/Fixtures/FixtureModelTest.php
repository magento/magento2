<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $reindexCommand = Bootstrap::getObjectManager()->get('Magento\Indexer\Console\Command\IndexerReindexCommand');
        $parser = Bootstrap::getObjectManager()->get('Magento\Framework\Xml\Parser');
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
        $appCache = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache');
        $appCache->clean(
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
            ]
        );
    }

    /**
     * Apply fixture file
     *
     * @param string $fixtureFilename
     */
    public function applyFixture($fixtureFilename)
    {
        require $fixtureFilename;
    }

    /**
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }
        return $this->_objectManager;
    }

    /**
     * Reset object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function resetObjectManager()
    {
        $this->_objectManager = null;
        return $this;
    }
}
