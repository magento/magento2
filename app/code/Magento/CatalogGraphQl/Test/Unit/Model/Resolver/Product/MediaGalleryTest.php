<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Entry;
use Magento\CatalogGraphQl\Model\Resolver\Product\MediaGallery;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\GraphQl\Config\Element\Field;

class MediaGalleryTest extends TestCase
{
    /**
     * @var Field|MockObject
     */
    private Field|MockObject $fieldMock;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $contextMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private ResolveInfo|MockObject $infoMock;

    /**
     * @var Product|MockObject
     */
    private Product|MockObject $productMock;

    /**
     * @var MediaGallery
     */
    private MediaGallery $mediaGallery;

    protected function setUp(): void
    {
        $this->fieldMock = $this->createMock(Field::class);
        $this->contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $this->infoMock = $this->createMock(ResolveInfo::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaGallery = new MediaGallery();
    }

    /**
     * @dataProvider dataProviderForResolve
     * @param $expected
     * @param $productName
     * @return void
     * @throws Exception
     */
    public function testResolve($expected, $productName): void
    {
        $existingEntryMock = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->addMethods(['getName'])
            ->onlyMethods(['getData', 'getExtensionAttributes'])
            ->getMock();
        $existingEntryMock->expects($this->any())->method('getData')->willReturn($expected);
        $existingEntryMock->expects($this->any())->method(
            'getExtensionAttributes'
        )->willReturn(false);
        $this->productMock->expects($this->any())->method('getName')->willReturn($productName);
        $this->productMock->expects($this->any())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $result = $this->mediaGallery->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->infoMock,
            [
                'model' => $this->productMock
            ],
            []
        );
        $this->assertNotEmpty($result);
        $this->assertEquals($productName, $result[0]['label']);
    }

    /**
     * @return array
     */
    public static function dataProviderForResolve(): array
    {
        return [
            [
                [
                    "file" => "/w/b/wb01-black-0.jpg",
                    "media_type" => "image",
                    "label" => null,
                    "position" => "1",
                    "disabled" => "0",
                    "types" => [
                        "image",
                        "small_image"
                    ],
                    "id" => "11"
                ],
                "TestImage"
            ],
            [
                [
                    "file" => "/w/b/wb01-black-0.jpg",
                    "media_type" => "image",
                    "label" => "HelloWorld",
                    "position" => "1",
                    "disabled" => "0",
                    "types" => [
                        "image",
                        "small_image"
                    ],
                    "id" => "11"
                ],
                "HelloWorld"
            ]
        ];
    }
}
