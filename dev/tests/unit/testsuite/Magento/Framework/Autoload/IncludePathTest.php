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
namespace Magento\Framework\Autoload;

class IncludePathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_originalPath = '';

    public static function setUpBeforeClass()
    {
        self::$_originalPath = get_include_path();
    }

    protected function tearDown()
    {
        set_include_path(self::$_originalPath);
    }

    /**
     * @param string $class
     * @param bool string|$expectedValue
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($class, $expectedValue)
    {
        $this->assertFalse((new \Magento\Framework\Autoload\IncludePath())->getFile($class));
        (new \Magento\Framework\Autoload\IncludePath())->addIncludePath(__DIR__ . '/_files');
        $this->assertEquals($expectedValue, (new \Magento\Framework\Autoload\IncludePath())->getFile($class));
    }

    /**
     * @return array
     */
    public function getFileDataProvider()
    {
        return array(
            array('TestClass', realpath(__DIR__ . '/_files/TestClass.php')),
            array('\Ns\TestClass', realpath(__DIR__ . '/_files/Ns/TestClass.php')),
            array('Non_Existing_Class', false)
        );
    }

    /**
     * @dataProvider addIncludePathDataProvider
     *
     * @param string|array $fixturePath
     * @param bool $prepend
     * @param string $expectedIncludePath
     */
    public function testAddIncludePath($fixturePath, $prepend, $expectedIncludePath)
    {
        $expectedIncludePath = str_replace('%include_path%', get_include_path(), $expectedIncludePath);
        $this->assertNotEquals($expectedIncludePath, get_include_path());
        (new \Magento\Framework\Autoload\IncludePath())->addIncludePath($fixturePath, $prepend);
        $this->assertEquals($expectedIncludePath, get_include_path());
    }

    public function addIncludePathDataProvider()
    {
        $pathSeparator = PATH_SEPARATOR;
        return array(
            'prepend string' => array('fixture_path', true, "fixture_path{$pathSeparator}%include_path%"),
            'prepend array' => array(
                array('fixture_path_one', 'fixture_path_two'),
                true,
                "fixture_path_one{$pathSeparator}fixture_path_two{$pathSeparator}%include_path%"
            ),
            'append string' => array('fixture_path', false, "%include_path%{$pathSeparator}fixture_path"),
            'append array' => array(
                array('fixture_path_one', 'fixture_path_two'),
                false,
                "%include_path%{$pathSeparator}fixture_path_one{$pathSeparator}fixture_path_two"
            )
        );
    }

    /**
     * @param string $class
     * @param string|bool $expectedValue
     * @dataProvider getFileDataProvider
     */
    public function testLoad($class, $expectedValue)
    {
        (new \Magento\Framework\Autoload\IncludePath())->addIncludePath(__DIR__ . '/_files');
        $this->assertFalse(class_exists($class, false));
        (new \Magento\Framework\Autoload\IncludePath())->load($class);
        if ($expectedValue) {
            $this->assertTrue(class_exists($class, false));
        } else {
            $this->assertFalse(class_exists($class, false));
        }
    }
}
