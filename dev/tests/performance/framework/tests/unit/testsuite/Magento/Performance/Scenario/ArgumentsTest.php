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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Performance_Scenario_ArgumentsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Scenario_Arguments
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new Magento_Performance_Scenario_Arguments(array(
            Magento_Performance_Scenario_Arguments::ARG_USERS => 10,
            Magento_Performance_Scenario_Arguments::ARG_LOOPS => 100,
        ));
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    /**
     * @dataProvider constructorDataProvider
     *
     * @param array $inputArgs
     * @param array $expectedArgs
     */
    public function testConstructor(array $inputArgs, array $expectedArgs)
    {
        $args = new Magento_Performance_Scenario_Arguments($inputArgs);
        $this->assertEquals($expectedArgs, (array)$args);
    }

    public function constructorDataProvider()
    {
        return array(
            'default arguments'   => array(
                array(),
                array(
                    Magento_Performance_Scenario_Arguments::ARG_USERS => 1,
                    Magento_Performance_Scenario_Arguments::ARG_LOOPS => 1,
                )
            ),
            'overriding argument "users"' => array(
                array(Magento_Performance_Scenario_Arguments::ARG_USERS => 2),
                array(
                    Magento_Performance_Scenario_Arguments::ARG_USERS => 2,
                    Magento_Performance_Scenario_Arguments::ARG_LOOPS => 1,
                )
            ),
            'overriding argument "loops"' => array(
                array(Magento_Performance_Scenario_Arguments::ARG_LOOPS => 5),
                array(
                    Magento_Performance_Scenario_Arguments::ARG_USERS => 1,
                    Magento_Performance_Scenario_Arguments::ARG_LOOPS => 5,
                )
            ),
        );
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     *
     * @param array $inputArgs
     * @param string $expectedExceptionMsg
     */
    public function testConstructorException(array $inputArgs, $expectedExceptionMsg)
    {
        $this->setExpectedException('UnexpectedValueException', $expectedExceptionMsg);
        new Magento_Performance_Scenario_Arguments($inputArgs);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'invalid argument "users"' => array(
                array(Magento_Performance_Scenario_Arguments::ARG_USERS => -1),
                "Scenario argument 'users' must be a positive integer."
            ),
            'invalid argument "loops"' => array(
                array(Magento_Performance_Scenario_Arguments::ARG_LOOPS => 'abc'),
                "Scenario argument 'loops' must be a positive integer."
            ),
        );
    }

    public function testGetUsers()
    {
        $this->assertEquals(10, $this->_object->getUsers());
    }

    public function testGetLoops()
    {
        $this->assertEquals(100, $this->_object->getLoops());
    }

    public function testOffsetSet()
    {
        try {
            $this->_object[Magento_Performance_Scenario_Arguments::ARG_LOOPS] = 100500;
        } catch (LogicException $e) {
            $this->assertEquals('Scenario arguments are read-only.', $e->getMessage());
            $this->testGetLoops();
        }
    }

    public function testOffsetUnset()
    {
        try {
            unset($this->_object[Magento_Performance_Scenario_Arguments::ARG_USERS]);
        } catch (LogicException $e) {
            $this->assertEquals('Scenario arguments are read-only.', $e->getMessage());
            $this->testGetUsers();
        }
    }
}
