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
namespace Magento\ObjectManager\Config\Argument\Interpreter;

use Magento\Stdlib\BooleanUtils;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param string $className
     * @param bool $isShared
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($data, $className, $isShared)
    {
        $expected = new \StdClass();
        $factory = $this->getMock(
            '\Magento\ObjectManager\Config\Argument\ObjectFactory',
            array('create'),
            array(),
            '',
            false
        );
        $factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $className,
            $isShared
        )->will(
            $this->returnValue($expected)
        );
        $interpreter = new Object(new BooleanUtils(), $factory);
        $this->assertSame($expected, $interpreter->evaluate($data));
    }

    /**
     * @return array
     */
    public function evaluateDataProvider()
    {
        return array(
            array(array('value' => 'Class'), 'Class', false),
            array(array('value' => 'Class', 'shared' => false), 'Class', false),
            array(array('value' => 'Class', 'shared' => 0), 'Class', false),
            array(array('value' => 'Class', 'shared' => '0'), 'Class', false),
            array(array('value' => 'Class', 'shared' => 'false'), 'Class', false),
            array(array('value' => 'Class', 'shared' => true), 'Class', true),
            array(array('value' => 'Class', 'shared' => 1), 'Class', true),
            array(array('value' => 'Class', 'shared' => '1'), 'Class', true),
            array(array('value' => 'Class', 'shared' => 'true'), 'Class', true)
        );
    }

    /**
     * @param array $data
     * @dataProvider evaluateErrorDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Object class name is missing.
     */
    public function testEvaluateNoClass($data)
    {
        $factory = $this->getMock('\Magento\ObjectManager\Config\Argument\ObjectFactory', array(), array(), '', false);
        $interpreter = new Object(new BooleanUtils(), $factory);
        $interpreter->evaluate($data);
    }

    /**
     * @return array
     */
    public function evaluateErrorDataProvider()
    {
        return array(
            array(array()),
            array(array('value' => '')),
            array(array('value' => false)),
            array(array('value' => 0))
        );
    }
}
