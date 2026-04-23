<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet getModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeSetTest extends AbstractModifierTestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected $attributeSetCollectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $attributeSetCollectionMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ProductResource|MockObject
     */
    protected $productResourceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attributeSetCollectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->attributeSetCollectionMock = $this->createMock(Collection::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->productResourceMock = $this->createMock(ProductResource::class);

        $this->attributeSetCollectionFactoryMock->method('create')->willReturn($this->attributeSetCollectionMock);
        $this->productMock->method('getResource')->willReturn($this->productResourceMock);
        $this->productMock->method('getAttributeSetId')->willReturn(4);
        $this->attributeSetCollectionMock->expects($this->any())
            ->method('setEntityTypeFilter')
            ->willReturnSelf();
        $this->attributeSetCollectionMock->method('addFieldToSelect')
            ->willReturnSelf();
        $this->attributeSetCollectionMock->method('setOrder')
            ->willReturnSelf();
        $this->attributeSetCollectionMock->method('getData')
            ->willReturn([]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(AttributeSet::class, [
            'locator' => $this->locatorMock,
            'attributeSetCollectionFactory' => $this->attributeSetCollectionFactoryMock,
            'urlBuilder' => $this->urlBuilderMock,
        ]);
    }

    public function testModifyMeta()
    {
        $modifyMeta = $this->getModel()->modifyMeta(['test_group' => []]);
        $this->assertNotEmpty($modifyMeta);
    }

    /**
     * @param bool $locked
     */
    #[DataProvider('modifyMetaLockedDataProvider')]
    public function testModifyMetaLocked($locked)
    {
        $this->productMock->method('isLockedAttribute')->willReturn($locked);
        $modifyMeta = $this->getModel()->modifyMeta([AbstractModifier::DEFAULT_GENERAL_PANEL => []]);
        $children = $modifyMeta[AbstractModifier::DEFAULT_GENERAL_PANEL]['children'];
        $this->assertEquals(
            $locked,
            $children['attribute_set_id']['arguments']['data']['config']['disabled']
        );
    }

    /**
     * @return array
     */
    public static function modifyMetaLockedDataProvider()
    {
        return [[true], [false]];
    }

    public function testModifyMetaToBeEmpty()
    {
        $this->assertEmpty($this->getModel()->modifyMeta([]));
    }

    public function testGetOptions()
    {
        $this->attributeSetCollectionMock->expects($this->once())
            ->method('getData')
            ->willReturn([]);

        $this->assertSame([], $this->getModel()->getOptions());
    }

    public function testModifyData()
    {
        $productId = 1;
        $attributeSetId = 4;

        $this->productMock->setData('entity_id', $productId);

        $result = $this->getModel()->modifyData([]);
        $this->assertArrayHasKey($productId, $result);
        $this->assertEquals(
            $attributeSetId,
            $result[$productId][AttributeSet::DATA_SOURCE_DEFAULT]['attribute_set_id']
        );
    }
}
