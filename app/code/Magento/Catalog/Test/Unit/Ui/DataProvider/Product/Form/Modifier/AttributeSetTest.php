<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

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
class AttributeSetTest extends AbstractModifierTest
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
        $this->attributeSetCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productResourceMock = $this->getMockBuilder(ProductResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeSetCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->attributeSetCollectionMock);
        $this->productMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->productResourceMock);
        $this->attributeSetCollectionMock->expects($this->any())
            ->method('setEntityTypeFilter')
            ->willReturnSelf();
        $this->attributeSetCollectionMock->expects($this->any())
            ->method('addFieldToSelect')
            ->willReturnSelf();
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
     * @dataProvider modifyMetaLockedDataProvider
     */
    public function testModifyMetaLocked($locked)
    {
        $this->productMock->expects($this->any())
            ->method('isLockedAttribute')
            ->willReturn($locked);
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
    public function modifyMetaLockedDataProvider()
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

        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->assertArrayHasKey($productId, $this->getModel()->modifyData([]));
    }
}
