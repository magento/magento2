<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test;

/**
 * Class ApplicationTest
 *
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_script;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var array
     */
    protected $_fixtureConfigData;

    /**
     * Set Up before test
     */
    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . '/Performance/_files';
        $this->_fixtureConfigData = require $this->_fixtureDir . '/config_data.php';

        $this->_script = realpath($this->_fixtureDir . '/app_base_dir/setup/index.php');

        $this->_config = new \Magento\TestFramework\Performance\Config(
            $this->_fixtureConfigData,
            $this->_fixtureDir,
            $this->_fixtureDir . '/app_base_dir'
        );
        $this->_shell = $this->getMock('Magento\Framework\Shell', ['execute'], [], '', false);
        $objectManager = $this->getMock('\Magento\Framework\ObjectManagerInterface');

        $this->_object = $this->getMock(
            'Magento\TestFramework\Application',
            ['_cleanupMage', '_reindex', '_updateFilesystemPermissions', 'getObjectManager'],
            [$this->_config, $objectManager, $this->_shell]
        );
        $this->_object->expects($this->any())->method('_reindex')->will($this->returnValue($this->_object));
        $this->_object->expects($this->any())->method('getObjectManager')->will($this->returnValue($objectManager));

        // For fixture testing
        $this->_object->applied = [];
    }

    /**
     * Tear down after test
     */
    protected function tearDown()
    {
        unset($this->_config);
        unset($this->_shell);
        unset($this->_object);
    }

    /**
     * Apply fixtures test
     *
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
     * Apply fixture data provider
     *
     * @return array
     */
    public function applyFixturesDataProvider()
    {
        return [
            'empty fixtures' => [[], []],
            'fixtures' => [$this->_getFixtureFiles(['fixture1', 'fixture2']), ['fixture1', 'fixture2']]
        ];
    }

    /**
     * Apply fixture test
     *
     * @param array $initialFixtures
     * @param array $subsequentFixtures
     * @param array $subsequentExpected
     * @dataProvider applyFixturesSeveralTimesDataProvider
     */
    public function testApplyFixturesSeveralTimes($initialFixtures, $subsequentFixtures, $subsequentExpected)
    {
        $this->_object->applyFixtures($initialFixtures);
        $this->_object->applied = [];
        $this->_object->applyFixtures($subsequentFixtures);
        $this->assertEquals($subsequentExpected, $this->_object->applied);
    }

    /**
     * Apply fixtture data provider
     *
     * @return array
     */
    public function applyFixturesSeveralTimesDataProvider()
    {
        return [
            'no fixtures applied, when sets are same' => [
                $this->_getFixtureFiles(['fixture1', 'fixture2']),
                $this->_getFixtureFiles(['fixture1', 'fixture2']),
                [],
            ],
            'missing fixture applied for a super set' => [
                $this->_getFixtureFiles(['fixture1']),
                $this->_getFixtureFiles(['fixture1', 'fixture2']),
                ['fixture2'],
            ],
            'fixtures are re-applied for an incompatible set' => [
                $this->_getFixtureFiles(['fixture1', 'fixture2']),
                $this->_getFixtureFiles(['fixture1']),
                ['fixture1'],
            ]
        ];
    }

    /**
     * Adds file paths to fixture in a list
     *
     * @param array $fixture
     *
     * @return array
     */
    protected function _getFixtureFiles($fixtures)
    {
        $result = [];
        foreach ($fixtures as $fixture) {
            $result[] = __DIR__ . "/_files/application_test/{$fixture}.php";
        }
        return $result;
    }

    /**
     * Apply fixture with install
     */
    public function testApplyFixturesInstallsApplication()
    {
        // Expect uninstall and install
        $this->_shell->expects(
            $this->at(0)
        )->method(
            'execute'
        )->with(
            $this->anything(),
            $this->contains($this->_script)
        );

        $this->_shell->expects(
            $this->at(1)
        )->method(
            'execute'
        )->with(
            $this->anything(),
            $this->contains($this->_script)
        );

        $fixture1 = $this->_getFixtureFiles(['fixture1']);
        $this->_object->applyFixtures($fixture1);
    }

    /**
     * Apply fixture w/o install
     */
    public function testApplyFixturesSuperSetNoInstallation()
    {
        // Initial uninstall/install only
        $this->_shell->expects($this->exactly(5))->method('execute');

        $fixture1 = $this->_getFixtureFiles(['fixture1']);
        $this->_object->applyFixtures($fixture1);
        $superSet = $this->_getFixtureFiles(['fixture1', 'fixture2']);
        $this->_object->applyFixtures($superSet);
    }

    /**
     * Apply fixtures test with no reinstall
     */
    public function testApplyFixturesIncompatibleSetReinstallation()
    {
        $this->_shell->expects(
            $this->at(0)
        )->method(
            'execute'
        )->with(
            $this->anything(),
            $this->contains($this->_script)
        );

        $this->_shell->expects(
            $this->at(1)
        )->method(
            'execute'
        )->with(
            $this->anything(),
            $this->contains($this->_script)
        );

        $fixtures = $this->_getFixtureFiles(['fixture1', 'fixture2']);
        $this->_object->applyFixtures($fixtures);
        $incompatibleSet = $this->_getFixtureFiles(['fixture1']);
        $this->_object->applyFixtures($incompatibleSet);
    }

    /**
     * Test application reset
     */
    public function testAppReset()
    {
        $this->assertEquals(true, $this->_object->reset() instanceof \Magento\TestFramework\Application);
    }
}
