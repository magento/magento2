<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType as AbstractType;

class AbstractTypeTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test attribute code name
     */
    const ATTR_CODE_NAME = 'attr_code_1';

    /**
     * Mock for abstractType
     *
     * @var AbstractType
     */
    protected $_abstractType;

    /**
     * Test product type attribute sets and attributes parameters
     *
     * @var array
     */
    protected $_attributes = [
        'attr_set_name_1' => [
            self::ATTR_CODE_NAME => [
                'options' => [],
                'type' => ['text', 'price', 'textarea', 'select'],
                'id' => 'id_1'
            ],
        ]
    ];

    /**
     * Expected attributes sets and attributes parameters
     *
     * @var array
     */
    protected $_expectedAttributes = [
        'attr_set_name_1' => [
            self::ATTR_CODE_NAME => [
                'options' => ['opt_key_1' => 'opt_val_1'],
                'type' => ['text', 'price', 'textarea', 'select'],
                'id' => 'id_1'
            ],
        ]
    ];

    /**
     * Test new option
     *
     * @var array
     */
    protected $_option = ['opt_key_1' => 'opt_val_1'];

    public function setUp()
    {
        $this->_abstractType = $this->getMockForAbstractClass('Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType', [], '', false);
    }

    /**
     * Test constructor on exception throwing in case of wrong params.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider constructorParamsDataProvider
     */
    public function testConstructorThrowException($params) {
        $classname = 'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType';
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->setMethods(array('_initAttributes'))
            ->getMockForAbstractClass();

        $reflectedClass = new \ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($mock, array(
            $this->getMock('\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory', [], [], '', false),
            $this->getMock('\Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',  [], [], '', false),
            $this->getMock('\Magento\Framework\App\Resource',  [], [], '', false),
            $params
        ));
    }

    /**
     * Test addAttributeOption()
     */
    public function testAddAttributeOption() {
        $classname = 'Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType';
        $reflectedClass = new \ReflectionClass($classname);
        $addAttributeOptionMethod = $reflectedClass->getMethod('addAttributeOption');

        $_attributesProperty = $reflectedClass->getProperty("_attributes");
        $_attributesProperty->setAccessible(TRUE);
        $_attributesProperty->setValue($this->_abstractType, $this->_attributes);

        $addAttributeOptionMethod->invokeArgs($this->_abstractType, array(
            self::ATTR_CODE_NAME, key($this->_option), current($this->_option)
        ));

        $this->assertEquals($this->_expectedAttributes, $_attributesProperty->getValue($this->_abstractType));
    }

    /**
     * Data provider constructor params argument
     *
     * @return array
     */
    public function constructorParamsDataProvider()
    {
        $mock = $this->getMockBuilder('\Magento\CatalogImportExport\Model\Import\Product')->disableOriginalConstructor()->getMock();
        return [
            [
                '$params' => [],
            ],
            [
                '$params' => [$mock],
            ],
            [
                '$params' => [new \stdClass(), 'default'],
            ],
        ];
    }

    /**
     * @todo implement it.
     */
    public function testGetParticularAttributes() {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo implement it.
     */
    public function testIsRowValid() {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo implement it.
     */
    public function testPrepareAttributesWithDefaultValueForSave() {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @todo implement it.
     */
    public function testClearEmptyData() {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
