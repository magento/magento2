<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Performance;

class ScenarioTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Scenario
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new \Magento\TestFramework\Performance\Scenario(
            'Test title',
            'test/file.jmx',
            ['arg1' => 'value1', 'arg2' => 'value2'],
            ['setting1' => 'value1', 'setting2' => 'value2'],
            ['fixture1', 'fixture2']
        );
    }

    protected function tearDown()
    {
        unset($this->_object);
    }

    public function testGetTitle()
    {
        $this->assertEquals('Test title', $this->_object->getTitle());
    }

    public function testGetFile()
    {
        $this->assertEquals('test/file.jmx', $this->_object->getFile());
    }

    public function testGetArguments()
    {
        $expectedArguments = [
            'arg1' => 'value1',
            'arg2' => 'value2',
            \Magento\TestFramework\Performance\Scenario::ARG_USERS => 1,
            \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 1,
        ];
        $this->assertEquals($expectedArguments, $this->_object->getArguments());
    }

    public function testGetSettings()
    {
        $expectedSettings = ['setting1' => 'value1', 'setting2' => 'value2'];
        $this->assertEquals($expectedSettings, $this->_object->getSettings());
    }

    public function testGetFixtures()
    {
        $expectedFixtures = ['fixture1', 'fixture2'];
        $this->assertEquals($expectedFixtures, $this->_object->getFixtures());
    }
}
