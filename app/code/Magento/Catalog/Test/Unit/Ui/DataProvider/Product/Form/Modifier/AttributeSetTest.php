<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

/**
 * Class AttributeSetTest
 *
 * @method \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet getModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeSetTest extends AbstractModifierTest
{
    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetCollectionFactoryMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetCollectionMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var ProductResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productResourceMock;

    protected function setUp()
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
            ->getMock();
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
        $this->assertNotEmpty($this->getModel()->modifyMeta(['test_group' => []]));
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
