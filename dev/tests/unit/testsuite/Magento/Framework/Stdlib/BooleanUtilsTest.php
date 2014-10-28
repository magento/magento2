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
namespace Magento\Framework\Stdlib;

class BooleanUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BooleanUtils
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new BooleanUtils();
    }

    public function testConstructor()
    {
        $object = new BooleanUtils(array('yep'), array('nope'));
        $this->assertTrue($object->toBoolean('yep'));
        $this->assertFalse($object->toBoolean('nope'));
    }

    /**
     * @param mixed $input
     * @param bool $expected
     *
     * @dataProvider toBooleanDataProvider
     */
    public function testToBoolean($input, $expected)
    {
        $actual = $this->object->toBoolean($input);
        $this->assertSame($expected, $actual);
    }

    public function toBooleanDataProvider()
    {
        return array(
            'boolean "true"' => array(true, true),
            'boolean "false"' => array(false, false),
            'boolean string "true"' => array('true', true),
            'boolean string "false"' => array('false', false),
            'boolean numeric "1"' => array(1, true),
            'boolean numeric "0"' => array(0, false),
            'boolean string "1"' => array('1', true),
            'boolean string "0"' => array('0', false)
        );
    }

    /**
     * @param mixed $input
     *
     * @dataProvider toBooleanExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Boolean value is expected
     */
    public function testToBooleanException($input)
    {
        $this->object->toBoolean($input);
    }

    public function toBooleanExceptionDataProvider()
    {
        return array(
            'boolean string "on"' => array('on'),
            'boolean string "off"' => array('off'),
            'boolean string "yes"' => array('yes'),
            'boolean string "no"' => array('no'),
            'boolean string "TRUE"' => array('TRUE'),
            'boolean string "FALSE"' => array('FALSE'),
            'empty string' => array(''),
            'null' => array(null)
        );
    }
}
