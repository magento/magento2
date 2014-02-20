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

namespace Magento\Webapi\Model\Config\ClassReflector;

/**
 * Type processor Test
 */
class TypeProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor */
    protected $_typeProcessor;

    /** @var \Magento\Webapi\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_helperMock;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->_helperMock = $this->getMock('Magento\Webapi\Helper\Data', [], [], '', false);
        $this->_typeProcessor = new \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor($this->_helperMock);
    }

    /**
     * Test Retrieving of processed types data.
     */
    public function testGetTypesData()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA']);
        $this->_typeProcessor->setTypeData('typeB', ['dataB']);
        $this->assertEquals(['typeA' => ['dataA'], 'typeB' => ['dataB']], $this->_typeProcessor->getTypesData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data type "NonExistentType" is not declared.
     */
    public function testGetTypeDataInvalidArgumentException()
    {
        $this->_typeProcessor->getTypeData('NonExistentType');
    }

    /**
     * Test retrieval of data type details for the given type name.
     */
    public function testGetTypeData()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA']);
        $this->assertEquals(['dataA'], $this->_typeProcessor->getTypeData('typeA'));
    }

    /**
     * Test data type details for the same type name set multiple times.
     */
    public function testSetTypeDataArrayMerge()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA1']);
        $this->_typeProcessor->setTypeData('typeA', ['dataA2']);
        $this->_typeProcessor->setTypeData('typeA', ['dataA3']);
        $this->assertEquals(['dataA1', 'dataA2', 'dataA3'], $this->_typeProcessor->getTypeData('typeA'));
    }

    public function testNormalizeType()
    {
        $this->assertEquals('blah', $this->_typeProcessor->normalizeType('blah'));
        $this->assertEquals('string', $this->_typeProcessor->normalizeType('str'));
        //$this->assertEquals('integer', $this->_typeProcessor->normalizeType('int'));
        $this->assertEquals('boolean', $this->_typeProcessor->normalizeType('bool'));
    }

    public function testIsTypeSimple()
    {
        $this->assertTrue($this->_typeProcessor->isTypeSimple('string'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('string[]'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('int'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('float'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('double'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('boolean'));
        $this->assertFalse($this->_typeProcessor->isTypeSimple('blah'));
    }

    public function testIsArrayType()
    {
        $this->assertFalse($this->_typeProcessor->isArrayType('string'));
        $this->assertTrue($this->_typeProcessor->isArrayType('string[]'));
    }

    public function getArrayItemType()
    {
        $this->assertEquals('string', $this->_typeProcessor->getArrayItemType('str[]'));
        $this->assertEquals('string', $this->_typeProcessor->getArrayItemType('string[]'));
        $this->assertEquals('integer', $this->_typeProcessor->getArrayItemType('int[]'));
        $this->assertEquals('boolean', $this->_typeProcessor->getArrayItemType('bool[]'));
    }

    public function testTranslateTypeName()
    {
        $this->assertEquals(
            'TestModule1V1EntityItem',
            $this->_typeProcessor->translateTypeName('\Magento\TestModule1\Service\V1\Entity\Item')
        );
        $this->assertEquals(
            'TestModule3V1EntityParameter[]',
            $this->_typeProcessor->translateTypeName('\Magento\TestModule3\Service\V1\Entity\Parameter[]')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter type "\Magento\TestModule3\V1\Parameter[]".
     */
    public function testTranslateTypeNameInvalidArgumentException()
    {
        $this->_typeProcessor->translateTypeName('\Magento\TestModule3\V1\Parameter[]');
    }

    public function testTranslateArrayTypeName()
    {
        $this->assertEquals('ArrayOfComplexType', $this->_typeProcessor->translateArrayTypeName('complexType'));
    }
}
