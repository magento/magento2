<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Attribute\LockValidator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LockValidatorTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var LockValidator
     */
    private $model;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->resource = $this->createMock(ResourceConnection::class);

        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->select = $this->createPartialMock(Select::class, ['reset', 'from', 'join', 'where', 'group', 'limit']);

        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        $this->metadataPoolMock->expects(self::once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->getMetaDataMock());

        $this->model = $helper->getObject(
            LockValidator::class,
            ['resource' => $this->resource]
        );
        $refClass = new \ReflectionClass(LockValidator::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setValue($this->model, $this->metadataPoolMock);
    }

    /**
     * @return void
     */
    public function testValidate(): void
    {
        $this->validate(false);
    }

    /**
     * @return EntityMetadata|MockObject
     */
    private function getMetaDataMock(): EntityMetadata
    {
        $metadata = $this->createMock(EntityMetadata::class);

        $metadata->expects(self::once())
            ->method('getLinkField')
            ->willReturn('entity_id');

        return $metadata;
    }

    /**
     * @return void
     */
    public function testValidateException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('This attribute is used in configurable products.');
        $this->validate(true);
    }

    /**
     * @param $exception
     *
     * @return void
     * @throws LocalizedException
     */
    public function validate($exception): void
    {
        $attrTable = 'someAttributeTable';
        $productTable = 'someProductTable';
        $attributeId = 333;
        $attributeSet = 'attrSet';

        $bind = ['attribute_id' => $attributeId, 'attribute_set_id' => $attributeSet];

        $object = $this->createPartialMockWithReflection(
            AbstractModel::class,
            ['getAttributeId', 'setAttributeId']
        );
        $object->method('getAttributeId')->willReturn($attributeId);

        $this->resource->expects($this->once())->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resource
            ->method('getTableName')
            ->willReturnCallback(fn($operation) => match ([$operation]) {
                ['catalog_product_super_attribute'] => $attrTable,
                ['catalog_product_entity'] => $productTable
            });

        $this->connectionMock->expects($this->once())->method('select')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())->method('fetchOne')
            ->with($this->select, $bind)
            ->willReturn($exception);

        $this->select->expects($this->once())->method('reset')
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('from')
            ->with(
                ['main_table' => $attrTable],
                ['psa_count' => 'COUNT(product_super_attribute_id)']
            )
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('join')
            ->with(
                ['entity' => $productTable],
                'main_table.product_id = entity.entity_id'
            )
            ->willReturn($this->select);
        $this->select->method('where')->willReturn($this->select);
        $this->select->expects($this->once())->method('group')
            ->with('main_table.attribute_id')
            ->willReturn($this->select);
        $this->select->expects($this->once())->method('limit')
            ->with(1)
            ->willReturn($this->select);

        $this->model->validate($object, $attributeSet);
    }
}
