<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Config
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

class ConfigTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityType;

    protected function setUp()
    {
        $this->_attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute::class);
        $this->_entityType = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $this->_readerMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Config\Reader::class);
        $this->_cacheMock = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);
        $this->_cacheId = 'eav_attributes';
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with($this->_cacheId)
            ->willReturn('');

        $serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $serializerMock->method('unserialize')
            ->willReturn([]);

        $this->_model = new \Magento\Eav\Model\Entity\Attribute\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_cacheId,
            $serializerMock
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
