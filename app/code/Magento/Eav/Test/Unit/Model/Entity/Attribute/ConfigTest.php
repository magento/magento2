<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Config
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheId;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityType;

    protected function setUp()
    {
        $this->_attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $this->_entityType = $this->getMock('Magento\Eav\Model\Entity\Type', [], [], '', false);
        $this->_readerMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Config\Reader',
            [],
            [],
            '',
            false
        );
        $this->_cacheMock = $this->getMock('Magento\Framework\App\Cache\Type\Config', [], [], '', false);
        $this->_cacheId = 'eav_attributes';
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo($this->_cacheId)
        )->will(
            $this->returnValue(serialize([]))
        );

        $this->_model = new \Magento\Eav\Model\Entity\Attribute\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_cacheId
        );
    }

    public function testGetLockedFieldsEmpty()
    {
        $this->_entityType->expects($this->once())->method('getEntityTypeCode')->will($this->returnValue('test_code'));
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->will(
            $this->returnValue($this->_entityType)
        );

        $this->_attribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code')
        );
        $result = $this->_model->getLockedFields($this->_attribute);
        $this->assertEquals([], $result);
    }

    public function testGetLockedFields()
    {
        $this->_entityType->expects(
            $this->once()
        )->method(
            'getEntityTypeCode'
        )->will(
            $this->returnValue('test_code1/test_code2')
        );
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->will(
            $this->returnValue($this->_entityType)
        );

        $this->_attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue('test_code'));
        $data = [
            'test_code1' => [
                'test_code2' => ['attributes' => ['test_code' => ['test_code1' => 'test_code1']]],
            ],
        ];
        $this->_model->merge($data);
        $result = $this->_model->getLockedFields($this->_attribute);
        $this->assertEquals(['test_code1' => 'test_code1'], $result);
    }

    public function testGetEntityAttributesLockedFields()
    {
        $data = [
            'entity_code' => [
                'attributes' => [
                    'attribute_code' => [
                        'attribute_data' => ['locked' => 'locked_field', 'code' => 'code_test'],
                    ],
                ],
            ],
        ];
        $this->_model->merge($data);
        $result = $this->_model->getEntityAttributesLockedFields('entity_code');
        $this->assertEquals(['attribute_code' => ['code_test']], $result);
    }
}
