<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Config\CatalogClone\Media;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Config\CatalogClone\Media\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Catalog\Model\Config\CatalogClone\Media\Image.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var Collection|MockObject
     */
    private $attributeCollection;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->eavConfig = $this->createMock(Config::class);

        $this->attributeCollection = $this->createMock(Collection::class);

        $this->attributeCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->attributeCollectionFactory->method('create')->willReturn(
            $this->attributeCollection
        );

        $this->attribute = $this->createMock(Attribute::class);

        $this->escaperMock = $this->createPartialMock(Escaper::class, ['escapeHtml']);

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Image::class,
            [
                'eavConfig' => $this->eavConfig,
                'attributeCollectionFactory' => $this->attributeCollectionFactory,
                'escaper' => $this->escaperMock,
            ]
        );
    }

    /**
     * @param string $actualLabel
     * @param string $expectedLabel
     * @return void
     */
    #[DataProvider('getPrefixesDataProvider')]
    public function testGetPrefixes(string $actualLabel, string $expectedLabel): void
    {
        $entityTypeId = 3;
        /** @var Type|MockObject $entityType */
        $entityType = $this->createMock(Type::class);
        $entityType->expects($this->once())->method('getId')->willReturn($entityTypeId);

        /** @var AbstractFrontend|MockObject $frontend */
        $frontend = $this->createMock(AbstractFrontend::class);
        $frontend->expects($this->once())->method('getLabel')->willReturn($actualLabel);

        $this->attributeCollection->expects($this->once())->method('setEntityTypeFilter')->with($entityTypeId);
        $this->attributeCollection->expects($this->once())->method('setFrontendInputTypeFilter')->with('media_image');

        $this->attribute->expects($this->once())->method('getAttributeCode')->willReturn('attributeCode');
        $this->attribute->expects($this->once())->method('getFrontend')->willReturn($frontend);

        $this->attributeCollection->method('getIterator')->willReturn(new \ArrayIterator([$this->attribute]));

        $this->eavConfig->expects($this->any())->method('getEntityType')->with(Product::ENTITY)
            ->willReturn($entityType);

        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($actualLabel)
            ->willReturn($expectedLabel);

        $this->assertEquals([['field' => 'attributeCode_', 'label' => $expectedLabel]], $this->model->getPrefixes());
    }

    /**
     * @return array
     */
    public static function getPrefixesDataProvider(): array
    {
        return [
            [
                'actualLabel' => 'testLabel',
                'expectedLabel' => 'testLabel',
            ],
            [
                'actualLabel' => '<media-image-attributelabel',
                'expectedLabel' => '&lt;media-image-attributelabel',
            ],
        ];
    }
}
