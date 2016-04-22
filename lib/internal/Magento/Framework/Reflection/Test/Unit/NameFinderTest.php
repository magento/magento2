<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace Magento\Framework\Reflection\Test\Unit;

use Zend\Code\Reflection\ClassReflection;
use Magento\Framework\Exception\SerializationException;

/**
 * NameFinder Unit Test
 */
class NameFinderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Reflection\NameFinder */
    protected $nameFinder;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->nameFinder = new \Magento\Framework\Reflection\NameFinder();
    }

    public function testFindSetterMethodName()
    {
        $class = new ClassReflection("\\Magento\\Framework\\Reflection\\Test\\Unit\\DataObject");
        $setterName = $this->nameFinder->findSetterMethodName($class, 'AttrName');
        $this->assertEquals("setAttrName", $setterName);

        $booleanSetterName = $this->nameFinder->findSetterMethodName($class, 'Active');
        $this->assertEquals("setIsActive", $booleanSetterName);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Property :"InvalidAttribute" does not exist in the provided class: \w+/
     */
    public function testFindSetterMethodNameInvalidAttribute()
    {
        $class = new ClassReflection("\\Magento\\Framework\\Reflection\\Test\\Unit\\DataObject");
        $this->nameFinder->findSetterMethodName($class, 'InvalidAttribute');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Property :"InvalidAttribute" does not exist in the provided class: \w+/
     */
    public function testFindSetterMethodNameWrongCamelCasedAttribute()
    {
        $class = new ClassReflection("\\Magento\\Framework\\Reflection\\Test\\Unit\\DataObject");
        $this->nameFinder->findSetterMethodName($class, 'ActivE');
    }
}
