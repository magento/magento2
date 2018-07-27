<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    public function testGetSetterMethodName()
    {
        $class = new ClassReflection("\\Magento\\Framework\\Reflection\\Test\\Unit\\DataObject");
        $setterName = $this->nameFinder->getSetterMethodName($class, 'AttrName');
        $this->assertEquals("setAttrName", $setterName);

        $booleanSetterName = $this->nameFinder->getSetterMethodName($class, 'Active');
        $this->assertEquals("setIsActive", $booleanSetterName);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Property :"InvalidAttribute" does not exist in the provided class: \w+/
     */
    public function testGetSetterMethodNameInvalidAttribute()
    {
        $class = new ClassReflection("\\Magento\\Framework\\Reflection\\Test\\Unit\\DataObject");
        $this->nameFinder->getSetterMethodName($class, 'InvalidAttribute');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Property :"InvalidAttribute" does not exist in the provided class: \w+/
     */
    public function testGetSetterMethodNameWrongCamelCasedAttribute()
    {
        $class = new ClassReflection("\\Magento\\Framework\\Reflection\\Test\\Unit\\DataObject");
        $this->nameFinder->getSetterMethodName($class, 'ActivE');
    }
}
