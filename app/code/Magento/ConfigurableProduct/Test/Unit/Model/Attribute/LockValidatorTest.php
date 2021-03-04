<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ConfigurableProduct\Model\Attribute\LockValidator;

class LockValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Attribute\LockValidator
     */
    private $model;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $select;

    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->setMethods(['select', 'fetchOne'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->setMethods(['reset', 'from', 'join', 'where', 'group', 'limit'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPoolMock->expects(self::once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->getMetaDataMock());

        $this->model = $helper->getObject(
            LockValidator::class,
            [
                'resource' => $this->resource
            ]
        );
        $refClass = new \ReflectionClass(LockValidator::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $this->metadataPoolMock);
    }

    public function testValidate()
    {
        $this->validate(false);
    }

    /**
     * @return EntityMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMetaDataMock()
    {
        $metadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata->expects(self::once())
            ->method('getLinkField')
            ->willReturn('entity_id');

        return $metadata;
    }

    /**
     */
    public function testValidateException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('This attribute is used in configurable products.');

        $this->validate(true);
    }

    /**
     * @param $exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($exception)
    {
        $attrTable = 'someAttributeTable';
        $productTable = 'someProductTable';
        $attributeId = 333;
        $attributeSet = 'attrSet';

        $bind = ['attribute_id' => $attributeId, 'attribute_set_id' => $attributeSet];

        /** @var \Magento\Framework\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject $object */
        $object = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['getAttributeId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())->method('getAttributeId')->willReturn($attributeId);

        $this->resource->expects($this->once())->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resource->expects($this->at(1))->method('getTableName')
            ->with($this->equalTo('catalog_product_super_attribute'))
            ->willReturn($attrTable);
        $this->resource->expects($this->at(2))->method('getTableName')
            ->with($this->equalTo('catalog_product_entity'))
            ->willReturn($productTable);

        $this->connectionMock->expects($this->once())->method('select')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())->method('fetchOne')
            ->with($this->equalTo($this->select), $this->equalTo($bind))
            ->willReturn($exception);

        $this->select->expects($this->once())->method('reset')
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('from')
            ->with(
                $this->equalTo(['main_table' => $attrTable]),
                $this->equalTo(['psa_count' => 'COUNT(product_super_attribute_id)'])
            )
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('join')
            ->with(
                $this->equalTo(['entity' => $productTable]),
                $this->equalTo('main_table.product_id = entity.entity_id')
            )
            ->willReturn($this->select);
        $this->select->expects($this->any())->method('where')
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('group')
            ->with($this->equalTo('main_table.attribute_id'))
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('limit')
            ->with($this->equalTo(1))
            ->willReturn($this->select);

        $this->model->validate($object, $attributeSet);
    }
}
