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

/**
 * Test for \Magento\Eav\Model\Validator\Attribute\Data
 */
namespace Magento\Eav\Model\Validator\Attribute;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing  \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * @dataProvider isValidDataProvider
     *
     * @param array $attributeData
     * @param array|bool $result
     * @param bool $expected
     * @param array $messages
     * @param array $data
     */
    public function testIsValid(
        $attributeData,
        $result,
        $expected,
        $messages,
        $data = array('attribute' => 'new_test')
    ) {
        $entity = $this->_getEntityMock();
        $attribute = $this->_getAttributeMock($attributeData);
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            array('create'),
            array(
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager'),
                'string' => $this->getMock('Magento\Framework\Stdlib\String')
            )
        );

        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $validator->setAttributes(array($attribute))->setData($data);
        if ($attribute->getDataModel() || $attribute->getFrontendInput()) {
            $dataModel = $this->_getDataModelMock($result);
            $attrDataFactory->expects(
                $this->once()
            )->method(
                'create'
            )->with(
                $attribute,
                $entity
            )->will(
                $this->returnValue($dataModel)
            );
        }
        $this->assertEquals($expected, $validator->isValid($entity));
        $this->assertEquals($messages, $validator->getMessages());
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        return array(
            'is_valid' => array(
                'attributeData' => array(
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text'
                ),
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => array()
            ),
            'is_invalid' => array(
                'attributeData' => array(
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text'
                ),
                'attributeReturns' => array('Error'),
                'isValid' => false,
                'messages' => array('attribute' => array('Error'))
            ),
            'no_data_models' => array(
                'attributeData' => array('attribute_code' => 'attribute', 'frontend_input' => 'text'),
                'attributeReturns' => array('Error'),
                'isValid' => false,
                'messages' => array('attribute' => array('Error'))
            ),
            'no_data_models_no_frontend_input' => array(
                'attributeData' => array('attribute_code' => 'attribute'),
                'attributeReturns' => array('Error'),
                'isValid' => true,
                'messages' => array()
            ),
            'no_data_for attribute' => array(
                'attributeData' => array(
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text'
                ),
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => array(),
                'setData' => array('attribute2' => 'new_test')
            ),
            'is_valid_data_from_entity' => array(
                'attributeData' => array(
                    'attribute_code' => 'attribute',
                    'data_model' => $this->_getDataModelMock(null),
                    'frontend_input' => 'text'
                ),
                'attributeReturns' => true,
                'isValid' => true,
                'messages' => array(),
                'setData' => array()
            )
        );
    }

    /**
     * Testing \Magento\Eav\Model\Validator\Attribute\Data::isValid
     *
     * In this test entity attributes are got from attribute collection.
     */
    public function testIsValidAttributesFromCollection()
    {
        /** @var \Magento\Eav\Model\Entity\AbstractEntity $resource */
        $resource = $this->getMockForAbstractClass('Magento\Eav\Model\Entity\AbstractEntity', array(), '', false);
        $attribute = $this->_getAttributeMock(
            array(
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text'
            )
        );
        $collection = $this->getMockBuilder('Magento\Framework\Object')->setMethods(array('getItems'))->getMock();
        $collection->expects($this->once())->method('getItems')->will($this->returnValue(array($attribute)));
        $entityType = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(array('getAttributeCollection'))
            ->getMock();
        $entityType->expects($this->once())->method('getAttributeCollection')->will($this->returnValue($collection));
        $entity = $this->_getEntityMock();
        $entity->expects($this->once())->method('getResource')->will($this->returnValue($resource));
        $entity->expects($this->once())->method('getEntityType')->will($this->returnValue($entityType));
        $dataModel = $this->_getDataModelMock(true);
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            array('create'),
            array(
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager'),
                'string' => $this->getMock('Magento\Framework\Stdlib\String')
            )
        );
        $attrDataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $attribute,
            $entity
        )->will(
            $this->returnValue($dataModel)
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);

        $validator->setData(array('attribute' => 'new_test_data'));
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @dataProvider whiteBlackListProvider
     * @param callable $callback
     */
    public function testIsValidBlackListWhiteListChecks($callback)
    {
        $attribute = $this->_getAttributeMock(
            array(
                'attribute_code' => 'attribute',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text'
            )
        );
        $secondAttribute = $this->_getAttributeMock(
            array(
                'attribute_code' => 'attribute2',
                'data_model' => $this->_getDataModelMock(null),
                'frontend_input' => 'text'
            )
        );
        $data = array('attribute' => 'new_test_data', 'attribute2' => 'some data');
        $entity = $this->_getEntityMock();
        $dataModel = $this->_getDataModelMock(true, $data['attribute']);
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            array('create'),
            array(
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager'),
                'string' => $this->getMock('Magento\Framework\Stdlib\String')
            )
        );
        $attrDataFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $attribute,
            $entity
        )->will(
            $this->returnValue($dataModel)
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $validator->setAttributes(array($attribute, $secondAttribute))->setData($data);
        $callback($validator);
        $this->assertTrue($validator->isValid($entity));
    }

    /**
     * @return array
     */
    public function whiteBlackListProvider()
    {
        $whiteCallback = function ($validator) {
            $validator->setAttributesWhiteList(array('attribute'));
        };

        $blackCallback = function ($validator) {
            $validator->setAttributesBlackList(array('attribute2'));
        };
        return array('white_list' => array($whiteCallback), 'black_list' => array($blackCallback));
    }

    public function testSetAttributesWhiteList()
    {
        $attributes = array('attr1', 'attr2', 'attr3');
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            array(),
            array(
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager'),
                'string' => $this->getMock('Magento\Framework\Stdlib\String')
            )
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $result = $validator->setAttributesWhiteList($attributes);
        $this->assertAttributeEquals($attributes, '_attributesWhiteList', $validator);
        $this->assertEquals($validator, $result);
    }

    public function testSetAttributesBlackList()
    {
        $attributes = array('attr1', 'attr2', 'attr3');
        $attrDataFactory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            array(),
            array(
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager'),
                'string' => $this->getMock('Magento\Framework\Stdlib\String')
            )
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($attrDataFactory);
        $result = $validator->setAttributesBlackList($attributes);
        $this->assertAttributeEquals($attributes, '_attributesBlackList', $validator);
        $this->assertEquals($validator, $result);
    }

    public function testAddErrorMessages()
    {
        $data = array('attribute1' => 'new_test', 'attribute2' => 'some data');
        $entity = $this->_getEntityMock();
        $firstAttribute = $this->_getAttributeMock(
            array(
                'attribute_code' => 'attribute1',
                'data_model' => $firstDataModel = $this->_getDataModelMock(array('Error1')),
                'frontend_input' => 'text'
            )
        );
        $secondAttribute = $this->_getAttributeMock(
            array(
                'attribute_code' => 'attribute2',
                'data_model' => $secondDataModel = $this->_getDataModelMock(array('Error2')),
                'frontend_input' => 'text'
            )
        );
        $expectedMessages = array('attribute1' => array('Error1'), 'attribute2' => array('Error2'));
        $expectedDouble = array('attribute1' => array('Error1', 'Error1'), 'attribute2' => array('Error2', 'Error2'));
        $factory = $this->getMock(
            'Magento\Eav\Model\AttributeDataFactory',
            array('create'),
            array(
                'objectManager' => $this->getMock('Magento\Framework\ObjectManager'),
                'string' => $this->getMock('Magento\Framework\Stdlib\String')
            )
        );
        $validator = new \Magento\Eav\Model\Validator\Attribute\Data($factory);
        $validator->setAttributes(array($firstAttribute, $secondAttribute))->setData($data);

        $factory->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $firstAttribute,
            $entity
        )->will(
            $this->returnValue($firstDataModel)
        );
        $factory->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            $secondAttribute,
            $entity
        )->will(
            $this->returnValue($secondDataModel)
        );
        $factory->expects(
            $this->at(2)
        )->method(
            'create'
        )->with(
            $firstAttribute,
            $entity
        )->will(
            $this->returnValue($firstDataModel)
        );
        $factory->expects(
            $this->at(3)
        )->method(
            'create'
        )->with(
            $secondAttribute,
            $entity
        )->will(
            $this->returnValue($secondDataModel)
        );

        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedMessages, $validator->getMessages());
        $this->assertFalse($validator->isValid($entity));
        $this->assertEquals($expectedDouble, $validator->getMessages());
    }

    /**
     * @param array $attributeData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAttributeMock($attributeData)
    {
        $attribute = $this->getMockBuilder(
            'Magento\Eav\Model\Attribute'
        )->setMethods(
            array('getAttributeCode', 'getDataModel', 'getFrontendInput', '__wakeup')
        )->disableOriginalConstructor()->getMock();
        if (isset($attributeData['attribute_code'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getAttributeCode'
            )->will(
                $this->returnValue($attributeData['attribute_code'])
            );
        }
        if (isset($attributeData['data_model'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getDataModel'
            )->will(
                $this->returnValue($attributeData['data_model'])
            );
        }
        if (isset($attributeData['frontend_input'])) {
            $attribute->expects(
                $this->any()
            )->method(
                'getFrontendInput'
            )->will(
                $this->returnValue($attributeData['frontend_input'])
            );
        }
        return $attribute;
    }

    /**
     * @param boolean $returnValue
     * @param string|null $argument
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDataModelMock($returnValue, $argument = null)
    {
        $dataModel = $this->getMockBuilder(
            'Magento\Eav\Model\Attribute\Data\AbstractData'
        )->disableOriginalConstructor()->setMethods(
            array('validateValue')
        )->getMockForAbstractClass();
        if ($argument) {
            $dataModel->expects(
                $this->once()
            )->method(
                'validateValue'
            )->with(
                $argument
            )->will(
                $this->returnValue($returnValue)
            );
        } else {
            $dataModel->expects($this->any())->method('validateValue')->will($this->returnValue($returnValue));
        }
        return $dataModel;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getEntityMock()
    {
        $entity = $this->getMockBuilder(
            'Magento\Framework\Model\AbstractModel'
        )->setMethods(
            array('getAttribute', 'getResource', 'getEntityType', '__wakeup')
        )->disableOriginalConstructor()->getMock();
        return $entity;
    }
}
