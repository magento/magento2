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
namespace Magento\Framework\Code\Generator\CodeGenerator;

class ZendTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Possible flags for assertion
     */
    const FLAG_CONST = 'const';

    const FLAG_STATIC = 'static';

    const FLAG_FINAL = 'final';

    const FLAG_ABSTRACT = 'abstract';

    const FLAG_REFERENCE = 'passedByReference';

    /**#@-*/

    /**
     * @var \Magento\Framework\Code\Generator\CodeGenerator\Zend
     */
    protected $_model;

    /**
     * Methods to verify flags
     *
     * @var array
     */
    protected $_flagVerification = array(
        self::FLAG_CONST => 'isConst',
        self::FLAG_STATIC => 'isStatic',
        self::FLAG_FINAL => 'isFinal',
        self::FLAG_ABSTRACT => 'isAbstract',
        self::FLAG_REFERENCE => 'getPassedByReference'
    );

    /**
     * Doc block test data
     *
     * @var array
     */
    protected $_docBlockData = array(
        'shortDescription' => 'test_short_description',
        'longDescription' => 'test_long_description',
        'tags' => array(
            'tag1' => array('name' => 'tag1', 'description' => 'data1'),
            'tag2' => array('name' => 'tag2', 'description' => 'data2')
        )
    );

    /**
     * Method test data
     *
     * @var array
     */
    protected $_methodData = array(
        'testMethod1' => array(
            'name' => 'testMethod1',
            'final' => true,
            'static' => true,
            'parameters' => array(
                array('name' => 'data', 'type' => 'array', 'defaultValue' => array(), 'passedByReference' => true)
            ),
            'body' => 'return 1;',
            'docblock' => array('shortDescription' => 'test short description')
        ),
        '_testMethod2' => array(
            'name' => '_testMethod2',
            'visibility' => 'private',
            'abstract' => true,
            'parameters' => array(
                array('name' => 'data', 'defaultValue' => 'test_default'),
                array('name' => 'flag', 'defaultValue' => true)
            ),
            'body' => 'return 2;',
            'docblock' => array(
                'shortDescription' => 'test short description',
                'longDescription' => 'test long description',
                'tags' => array(
                    'tag1' => array('name' => 'tag1', 'description' => 'data1'),
                    'tag2' => array('name' => 'tag2', 'description' => 'data2')
                )
            )
        ),
        'testMethod3' => array('name' => 'testMethod3', 'body' => 'return 3;')
    );

    /**
     * Property test data
     *
     * @var array
     */
    protected $_propertyData = array(
        'TEST_CONSTANT' => array(
            'name' => 'TEST_CONSTANT',
            'const' => true,
            'defaultValue' => 'default constant value',
            'docblock' => array('shortDescription' => 'test description')
        ),
        '_protectedProperty' => array(
            'name' => '_protectedProperty',
            'visibility' => 'protected',
            'static' => 'true',
            'docblock' => array(
                'shortDescription' => 'Object Manager instance',
                'tags' => array('var' => array('name' => 'var', 'description' => 'tag description'))
            )
        ),
        'publicProperty' => array('name' => 'publicProperty')
    );

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Code\Generator\CodeGenerator\Zend();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testSetClassDocBlock()
    {
        $this->_model->setClassDocBlock($this->_docBlockData);
        $actualDocBlock = $this->_model->getDocBlock();

        $this->_assertDocBlockData($this->_docBlockData, $actualDocBlock);
    }

    /**
     * @param array $expectedDocBlock
     * @param \Zend\Code\Generator\DocBlockGenerator $actualDocBlock
     */
    protected function _assertDocBlockData(
        array $expectedDocBlock,
        \Zend\Code\Generator\DocBlockGenerator $actualDocBlock
    ) {
        // assert plain string data
        foreach ($expectedDocBlock as $propertyName => $propertyData) {
            if (is_string($propertyData)) {
                $this->assertAttributeEquals($propertyData, $propertyName, $actualDocBlock);
            }
        }

        // assert tags
        if (isset($expectedDocBlock['tags'])) {
            $expectedTagsData = $expectedDocBlock['tags'];
            $actualTags = $actualDocBlock->getTags();
            $this->assertSameSize($expectedTagsData, $actualTags);
            /** @var $actualTag \Zend\Code\Generator\DocBlock\Tag */
            foreach ($actualTags as $actualTag) {
                $tagName = $actualTag->getName();
                $this->assertArrayHasKey($tagName, $expectedTagsData);
                $this->assertEquals($expectedTagsData[$tagName]['name'], $tagName);
                $this->assertEquals($expectedTagsData[$tagName]['description'], $actualTag->getDescription());
            }
        }
    }

    public function testAddMethods()
    {
        $this->_model->addMethods($this->_methodData);
        $actualMethods = $this->_model->getMethods();

        $this->assertSameSize($this->_methodData, $actualMethods);

        /** @var $method \Zend\Code\Generator\MethodGenerator */
        foreach ($actualMethods as $methodName => $method) {
            $this->assertArrayHasKey($methodName, $this->_methodData);
            $expectedMethodData = $this->_methodData[$methodName];

            $this->assertEquals($expectedMethodData['name'], $method->getName());
            $this->assertEquals($expectedMethodData['body'], $method->getBody());

            // assert flags
            $this->_assertFlag(self::FLAG_STATIC, $expectedMethodData, $method);
            $this->_assertFlag(self::FLAG_FINAL, $expectedMethodData, $method);
            $this->_assertFlag(self::FLAG_ABSTRACT, $expectedMethodData, $method);

            // assert visibility
            $this->_assertVisibility($expectedMethodData, $method);

            // assert parameters
            if (isset($expectedMethodData['parameters'])) {
                $actualParameters = $method->getParameters();
                $this->assertSameSize($expectedMethodData['parameters'], $actualParameters);
                foreach ($expectedMethodData['parameters'] as $parameterData) {
                    $parameterName = $parameterData['name'];
                    $this->assertArrayHasKey($parameterName, $actualParameters);
                    /** @var $actualParameter \Zend\Code\Generator\ParameterGenerator */
                    $actualParameter = $actualParameters[$parameterName];
                    $this->assertEquals($parameterName, $actualParameter->getName());

                    // assert reference flag
                    $this->_assertFlag(self::FLAG_REFERENCE, $parameterData, $actualParameter);

                    // assert parameter type
                    if (isset($parameterData['type'])) {
                        $this->assertEquals($parameterData['type'], $actualParameter->getType());
                    }

                    // assert default value
                    if (isset($parameterData['defaultValue'])) {
                        /** @var $actualDefaultValue \Zend\Code\Generator\ValueGenerator */
                        $actualDefaultValue = $actualParameter->getDefaultValue();
                        $this->assertEquals($parameterData['defaultValue'], $actualDefaultValue->getValue());
                    }
                }
            }

            // assert docblock
            if (isset($expectedMethodData['docblock'])) {
                $actualDocBlock = $method->getDocBlock();
                $this->_assertDocBlockData($expectedMethodData['docblock'], $actualDocBlock);
            }
        }
    }

    /**
     * @param string $flagType
     * @param array $expectedData
     * @param object $actualObject
     */
    protected function _assertFlag($flagType, array $expectedData, $actualObject)
    {
        $expectedFlagValue = isset($expectedData[$flagType]) && $expectedData[$flagType];
        $flagGetter = $this->_flagVerification[$flagType];
        $this->assertEquals($expectedFlagValue, $actualObject->{$flagGetter}());
    }

    /**
     * @param array $expectedData
     * @param \Zend\Code\Generator\AbstractMemberGenerator $actualObject
     */
    protected function _assertVisibility(
        array $expectedData,
        \Zend\Code\Generator\AbstractMemberGenerator $actualObject
    ) {
        $expectedVisibility = isset($expectedData['visibility']) ? $expectedData['visibility'] : 'public';
        $this->assertEquals($expectedVisibility, $actualObject->getVisibility());
    }

    /**
     * Correct behaviour of addMethodFromGenerator is already tested in testAddMethods
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage addMethodFromGenerator() expects string for name
     */
    public function testAddMethodFromGenerator()
    {
        $invalidMethod = new \Zend\Code\Generator\MethodGenerator();
        $this->_model->addMethodFromGenerator($invalidMethod);
    }

    public function testAddProperties()
    {
        $this->_model->addProperties($this->_propertyData);
        $actualProperties = $this->_model->getProperties();

        $this->assertSameSize($this->_propertyData, $actualProperties);

        /** @var $property \Zend\Code\Generator\PropertyGenerator */
        foreach ($actualProperties as $propertyName => $property) {
            $this->assertArrayHasKey($propertyName, $this->_propertyData);
            $expectedPropertyData = $this->_propertyData[$propertyName];

            $this->assertEquals($expectedPropertyData['name'], $property->getName());

            // assert flags
            $this->_assertFlag(self::FLAG_CONST, $expectedPropertyData, $property);
            $this->_assertFlag(self::FLAG_STATIC, $expectedPropertyData, $property);

            // assert visibility
            $this->_assertVisibility($expectedPropertyData, $property);

            // assert default value
            if (isset($expectedPropertyData['defaultValue'])) {
                /** @var $actualDefaultValue \Zend\Code\Generator\ValueGenerator */
                $actualDefaultValue = $property->getDefaultValue();
                $this->assertEquals($expectedPropertyData['defaultValue'], $actualDefaultValue->getValue());
            }

            // assert docblock
            if (isset($expectedPropertyData['docblock'])) {
                $actualDocBlock = $property->getDocBlock();
                $this->_assertDocBlockData($expectedPropertyData['docblock'], $actualDocBlock);
            }
        }
    }

    /**
     * Correct behaviour of addPropertyFromGenerator is already tested in testAddProperties
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage addPropertyFromGenerator() expects string for name
     */
    public function testAddPropertyFromGenerator()
    {
        $invalidProperty = new \Zend\Code\Generator\PropertyGenerator();
        $this->_model->addPropertyFromGenerator($invalidProperty);
    }
}
