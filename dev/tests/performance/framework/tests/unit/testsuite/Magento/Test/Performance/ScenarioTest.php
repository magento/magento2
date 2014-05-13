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
            array('arg1' => 'value1', 'arg2' => 'value2'),
            array('setting1' => 'value1', 'setting2' => 'value2'),
            array('fixture1', 'fixture2')
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
        $expectedArguments = array(
            'arg1' => 'value1',
            'arg2' => 'value2',
            \Magento\TestFramework\Performance\Scenario::ARG_USERS => 1,
            \Magento\TestFramework\Performance\Scenario::ARG_LOOPS => 1
        );
        $this->assertEquals($expectedArguments, $this->_object->getArguments());
    }

    public function testGetSettings()
    {
        $expectedSettings = array('setting1' => 'value1', 'setting2' => 'value2');
        $this->assertEquals($expectedSettings, $this->_object->getSettings());
    }

    public function testGetFixtures()
    {
        $expectedFixtures = array('fixture1', 'fixture2');
        $this->assertEquals($expectedFixtures, $this->_object->getFixtures());
    }
}
