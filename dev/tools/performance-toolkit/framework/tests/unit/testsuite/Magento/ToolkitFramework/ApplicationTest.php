<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ToolkitFramework;

/**
 * Class ApplicationTest
 *
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Shell|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var \Magento\ToolkitFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_applicationBaseDir;

    /**
     * Set Up before test
     *
     * @return void
     */
    protected function setUp()
    {
        $this->_applicationBaseDir = __DIR__ . '/../../../../../bootstrap.php';
        $this->_shell = $this->getMock('Magento\Framework\Shell', ['execute'], [], '', false);

        $this->_object = new \Magento\ToolkitFramework\Application($this->_applicationBaseDir, $this->_shell);

        $this->_object->applied = []; // For fixture testing
    }

    /**
     * Tear down after test
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->_shell);
        unset($this->_object);
    }

    /**
     * Apply fixtures test
     *
     * @param array $fixtures
     * @param array $expected
     * @dataProvider applyFixturesDataProvider
     *
     * @return void
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
            'empty fixtures' => [
                [],
                [],
            ],
            'fixtures' => [
                $this->_getFixtureFiles(['fixture1', 'fixture2']),
                ['fixture1', 'fixture2'],
            ],
        ];
    }

    /**
     * Apply fixture test
     *
     * @param array $initialFixtures
     * @param array $subsequentFixtures
     * @param array $subsequentExpected
     * @dataProvider applyFixturesSeveralTimesDataProvider
     *
     * @return void
     */
    public function testApplyFixturesSeveralTimes($initialFixtures, $subsequentFixtures, $subsequentExpected)
    {
        $this->_object->applyFixtures($initialFixtures);
        $this->_object->applied = [];
        $this->_object->applyFixtures($subsequentFixtures);
        $this->assertEquals($subsequentExpected, $this->_object->applied);
    }

    /**
     * Apply fixture data provider
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
            'no fixtures applied, when sets were exist before' => [
                $this->_getFixtureFiles(['fixture1', 'fixture2']),
                $this->_getFixtureFiles(['fixture1']),
                [],
            ],
        ];
    }

    /**
     * Get fixture files
     *
     * @param array $fixtures
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
}
