<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    public static function setUpBeforeClass()
    {

        self::$_generatorWorkingDir = realpath(__DIR__ . '/../../../../../tools/performance-toolkit');
        (new \Magento\Framework\Autoload\IncludePath())->addIncludePath([self::$_generatorWorkingDir . '/framework']);
        copy(
            self::$_generatorWorkingDir . '/fixtures/tax_rates.csv',
            self::$_generatorWorkingDir . '/fixtures/tax_rates.csv.bak'
        );
        copy(__DIR__ . '/_files/tax_rates.csv', self::$_generatorWorkingDir . '/fixtures/tax_rates.csv');
        parent::setUpBeforeClass();
    }

    public function testTest()
    {
        $fixturesArray = \Magento\ToolkitFramework\FixtureSet::getInstance()->getFixtures();
        $config = \Magento\ToolkitFramework\Config::getInstance();
        $config->loadConfig(self::$_generatorWorkingDir . '/profiles/small.xml');

        foreach ($fixturesArray as $fixture) {
            $this->applyFixture(self::$_generatorWorkingDir . '/fixtures/' . $fixture['file']);
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
     * @return \Magento\Framework\ObjectManager
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
     * @return \Magento\Framework\ObjectManager
     */
    public function resetObjectManager()
    {
        $this->_objectManager = null;
        return $this;
    }
}
