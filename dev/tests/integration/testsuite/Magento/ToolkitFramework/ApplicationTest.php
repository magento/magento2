<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ToolkitFramework;

/**
 * Class Application test
 */
class ApplicationTest extends \Magento\TestFramework\Indexer\TestCase
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
        self::$_generatorWorkingDir = realpath(__DIR__ . '/../../../../../tools/performance-toolkit');
        copy(
            self::$_generatorWorkingDir . '/fixtures/tax_rates.csv',
            self::$_generatorWorkingDir . '/fixtures/tax_rates.csv.bak'
        );
        copy(__DIR__ . '/_files/tax_rates.csv', self::$_generatorWorkingDir . '/fixtures/tax_rates.csv');
        parent::setUpBeforeClass();
    }

    public function testTest()
    {
        $config = \Magento\ToolkitFramework\Config::getInstance();
        $config->loadConfig(__DIR__ . '/_files/small.xml');
        /** @var \Magento\TestFramework\Application $itfApplication */
        $itfApplication = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()->getApplication();
        $shell = $this->getMock('Magento\Framework\Shell', [], [], '', false);

        $application = new \Magento\ToolkitFramework\Application(
            $itfApplication->getTempDir(),
            $shell,
            $itfApplication->getInitParams()
        );

        $application->bootstrap();
        foreach ($application->loadFixtures()->getFixtures() as $fixture) {
            $fixture->execute();
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        unlink(self::$_generatorWorkingDir . '/fixtures/tax_rates.csv');
        rename(
            self::$_generatorWorkingDir . '/fixtures/tax_rates.csv.bak',
            self::$_generatorWorkingDir . '/fixtures/tax_rates.csv'
        );
        /** @var $appCache \Magento\Framework\App\Cache */
        $appCache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache');
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
