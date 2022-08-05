<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Config;
use Magento\Eav\Model\Entity\Attribute\Config\Reader;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Config
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_readerMock;

    /**
     * @var MockObject
     */
    protected $_cacheMock;

    /**
     * @var MockObject
     */
    protected $_cacheId;

    /**
     * @var Attribute|MockObject
     */
    protected $_attribute;

    /**
     * @var MockObject
     */
    protected $_entityType;

    protected function setUp(): void
    {
        $this->_attribute = $this->createMock(Attribute::class);
        $this->_entityType = $this->createMock(Type::class);
        $this->_readerMock = $this->createMock(Reader::class);
        $this->_cacheMock = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);
        $this->_cacheId = 'eav_attributes';
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with($this->_cacheId)
            ->willReturn('');

        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('unserialize')
            ->willReturn([]);

        $this->_model = new Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_cacheId,
            $serializerMock
        );
    }

    public function testGetLockedFieldsEmpty()
    {
        $this->_entityType->expects($this->once())->method('getEntityTypeCode')->willReturn('test_code');
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->willReturn(
            $this->_entityType
        );

        $this->_attribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->willReturn(
            'attribute_code'
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
        )->willReturn(
            'test_code1/test_code2'
        );
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->willReturn(
            $this->_entityType
        );

        $this->_attribute->expects($this->once())->method('getAttributeCode')->willReturn('test_code');
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
