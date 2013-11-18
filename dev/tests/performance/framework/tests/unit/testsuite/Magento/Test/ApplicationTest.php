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
 * @category    Magento
 * @package     performance_tests
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_config;

    /**
     * @var \Magento\Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\TestFramework\Application|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_installerScript;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var array
     */
    protected $_fixtureConfigData;

    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . '/Performance/_files';
        $this->_fixtureConfigData = require($this->_fixtureDir . '/config_data.php');

        $this->_installerScript = realpath($this->_fixtureDir . '/app_base_dir//dev/shell/install.php');

        $this->_config = new \Magento\TestFramework\Performance\Config(
            $this->_fixtureConfigData, $this->_fixtureDir, $this->_fixtureDir . '/app_base_dir'
        );
        $this->_shell = $this->getMock('Magento\Shell', array('execute'));

        $this->_object = $this->getMock(
            'Magento\TestFramework\Application',
            array('_bootstrap', '_cleanupMage', '_reindex', '_updateFilesystemPermissions'),
            array($this->_config, $this->_shell)
        );
        $this->_object->expects($this->any())
            ->method('_reindex')
            ->will($this->returnValue($this->_object));

        $this->_object->applied = array(); // For fixture testing
    }

    protected function tearDown()
    {
        unset($this->_config);
        unset($this->_shell);
        unset($this->_object);
    }

    /**
     * @expectedException \Magento\Exception
     */
    public function testConstructorException()
    {
        $invalidAppDir = __DIR__;
        new \Magento\TestFramework\Application(
            new \Magento\TestFramework\Performance\Config($this->_fixtureConfigData, $this->_fixtureDir,
                $invalidAppDir),
            $this->_shell
        );
    }


    /**
     * @param array $fixtures
     * @param array $expected
     * @dataProvider applyFixturesDataProvider
     */
    public function testApplyFixtures($fixtures, $expected)
    {
        $this->_object->applyFixtures($fixtures);
        $this->assertEquals($expected, $this->_object->applied);
    }

    /**
     * @return array
     */
    public function applyFixturesDataProvider()
    {

        return array(
            'empty fixtures' => array(
                array(),
                array()
            ),
            'fixtures' => array(
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                array('fixture1', 'fixture2')
            ),
        );
    }

    /**
     * @param array $initialFixtures
     * @param array $subsequentFixtures
     * @param array $subsequentExpected
     * @dataProvider applyFixturesSeveralTimesDataProvider
     */
    public function testApplyFixturesSeveralTimes($initialFixtures, $subsequentFixtures, $subsequentExpected)
    {
        $this->_object->applyFixtures($initialFixtures);
        $this->_object->applied = array();
        $this->_object->applyFixtures($subsequentFixtures);
        $this->assertEquals($subsequentExpected, $this->_object->applied);
    }

    /**
     * @return array
     */
    public function applyFixturesSeveralTimesDataProvider()
    {

        return array(
            'no fixtures applied, when sets are same' => array(
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                array()
            ),
            'missing fixture applied for a super set' => array(
                $this->_getFixtureFiles(array('fixture1')),
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                array('fixture2')
            ),
            'fixtures are re-applied for an incompatible set' => array(
                $this->_getFixtureFiles(array('fixture1', 'fixture2')),
                $this->_getFixtureFiles(array('fixture1')),
                array('fixture1')
            ),
        );
    }

    /**
     * Adds file paths to fixture in a list
     *
     * @param array $fixture
     * @return array
     */
    protected function _getFixtureFiles($fixtures)
    {
        $result = array();
        foreach ($fixtures as $fixture) {
            $result[] = __DIR__ . "/_files/application_test/{$fixture}.php";
        }
        return $result;
    }

    public function testApplyFixturesInstallsApplication()
    {
        // Expect uninstall and install
        $this->_shell->expects($this->at(0))
            ->method('execute')
            ->with($this->stringContains('--uninstall'), $this->contains($this->_installerScript));

        $this->_shell->expects($this->at(1))
            ->method('execute')
            ->with($this->logicalNot($this->stringContains('--uninstall')), $this->contains($this->_installerScript));

        $fixture1 = $this->_getFixtureFiles(array('fixture1'));
        $this->_object->applyFixtures($fixture1);
    }

    public function testApplyFixturesSuperSetNoInstallation()
    {
        $this->_shell->expects($this->exactly(2)) // Initial uninstall/install only
            ->method('execute');

        $fixture1 = $this->_getFixtureFiles(array('fixture1'));
        $this->_object->applyFixtures($fixture1);
        $superSet = $this->_getFixtureFiles(array('fixture1', 'fixture2'));
        $this->_object->applyFixtures($superSet);
    }

    public function testApplyFixturesIncompatibleSetReinstallation()
    {
        $this->_shell->expects($this->at(0))
            ->method('execute')
            ->with($this->stringContains('--uninstall'), $this->contains($this->_installerScript));

        $this->_shell->expects($this->at(1))
            ->method('execute')
            ->with($this->logicalNot($this->stringContains('--uninstall')), $this->contains($this->_installerScript));

        $this->_shell->expects($this->at(2))
            ->method('execute')
            ->with($this->stringContains('--uninstall'), $this->contains($this->_installerScript));

        $this->_shell->expects($this->at(3))
            ->method('execute')
            ->with($this->logicalNot($this->stringContains('--uninstall')), $this->contains($this->_installerScript));

        $fixtures = $this->_getFixtureFiles(array('fixture1', 'fixture2'));
        $this->_object->applyFixtures($fixtures);
        $incompatibleSet = $this->_getFixtureFiles(array('fixture1'));
        $this->_object->applyFixtures($incompatibleSet);
    }
}
