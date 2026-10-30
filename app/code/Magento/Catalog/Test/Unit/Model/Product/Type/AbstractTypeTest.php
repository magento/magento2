<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Type;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractTypeTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var Simple|MockObject
     */
    private $model;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var ProductResource|MockObject
     */
    private $productResource;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(Simple::class);

        $this->product = $this->createPartialMockWithReflection(
            Product::class,
            ['getHasOptions', '__sleep', 'getResource', 'getStatus']
        );
        $this->productResource = $this->createPartialMock(
            ProductResource::class,
            ['getSortedAttributes', 'loadAllAttributes']
        );

        $this->product->method('getResource')->willReturn($this->productResource);

        $this->attribute = $this->createPartialMockWithReflection(
            Attribute::class,
            ['getGroupSortPath', 'getSortPath']
        );
    }

    public function testIsSalable()
    {
        $this->product->method('getStatus')->willReturn(
            Status::STATUS_ENABLED
        );
        $this->product->setData('is_salable', 3);
        $this->assertTrue($this->model->isSalable($this->product));
    }

    public function testGetAttributeById()
    {
        $this->productResource->expects($this->any())->method('loadAllAttributes')->willReturn(
            $this->productResource
        );
        $this->productResource->expects($this->any())->method('getSortedAttributes')->willReturn(
            [$this->attribute]
        );
        $this->attribute->setId(1);

        $this->assertEquals($this->attribute, $this->model->getAttributeById(1, $this->product));
        $this->assertNull($this->model->getAttributeById(0, $this->product));
    }

    #[DataProvider('attributeCompareProvider')]
    public function testAttributesCompare($attr1, $attr2, $expectedResult)
    {
        $attribute = $this->attribute;
        $attribute->expects($this->any())->method('getSortPath')->willReturn(1);

        $attribute2 = clone $attribute;

        $attribute->expects($this->any())->method('getGroupSortPath')->willReturn($attr1);
        $attribute2->expects($this->any())->method('getGroupSortPath')->willReturn($attr2);

        $this->assertEquals($expectedResult, $this->model->attributesCompare($attribute, $attribute2));
    }

    /**
     * @return array
     */
    public static function attributeCompareProvider()
    {
        return [
            [2, 2, 0],
            [2, 1, 1],
            [1, 2, -1]
        ];
    }

    public function testGetSetAttributes()
    {
        $this->productResource->expects($this->any())->method('loadAllAttributes')->willReturn(
            $this->productResource
        );
        $this->productResource->expects($this->any())->method('getSortedAttributes')->willReturn(5);
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
        //Call the method for a second time, the cached copy should be used
        $this->assertEquals(5, $this->model->getSetAttributes($this->product));
    }

    public function testHasOptions()
    {
        $this->product->expects($this->once())->method('getHasOptions')->willReturn(true);
        $this->assertTrue($this->model->hasOptions($this->product));
    }

    public function testPrepareForCartAdvancedRejectsBuyRequestWithInvalidUtf8(): void
    {
        $model = $this->objectManagerHelper->getObject(Simple::class, ['serializer' => new Json()]);
        $product = $this->createPartialMock(
            Product::class,
            ['prepareCustomOptions', 'addCustomOption']
        );
        $product->expects($this->never())->method('addCustomOption');

        $result = $model->prepareForCartAdvanced(
            new DataObject(['product' => 4, 'qty' => 1, 'note' => "\xFF\xFE"]),
            $product
        );

        $this->assertSame('The request contains invalid characters.', $result);
    }

    public function testPrepareForCartAdvancedStoresSerializedBuyRequest(): void
    {
        $model = $this->objectManagerHelper->getObject(Simple::class, ['serializer' => new Json()]);
        $product = $this->createPartialMock(
            Product::class,
            ['prepareCustomOptions', 'addCustomOption']
        );
        $product->expects($this->once())
            ->method('addCustomOption')
            ->with('info_buyRequest', '{"product":4,"qty":1,"note":"gift"}');

        $result = $model->prepareForCartAdvanced(
            new DataObject(['product' => 4, 'qty' => 1, 'note' => 'gift']),
            $product
        );

        $this->assertSame([$product], $result);
        $this->assertSame(1.0, $product->getQty());
    }
}
