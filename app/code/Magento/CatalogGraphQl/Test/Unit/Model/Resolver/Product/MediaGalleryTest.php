<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product;

use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->infoMock = $this->createMock(ResolveInfo::class);
        $this->productMock = $this->createMock(Product::class);
        $this->mediaGallery = new MediaGallery();
    }

    /**
     * @param $expected
     * @param $productName
     * @return void
     * @throws Exception
     */
    #[DataProvider('dataProviderForResolve')]
    public function testResolve($expected, $productName): void
    {
        // Create a mock for Entry with getExtensionAttributes method
        $existingEntryMock = $this->createPartialMock(Entry::class, ['getExtensionAttributes']);
        $existingEntryMock->method('getExtensionAttributes')->willReturn(false);
        $existingEntryMock->setData($expected);
        
        $this->productMock->method('getName')->willReturn($productName);
        $this->productMock->method('getMediaGalleryEntries')->willReturn([$existingEntryMock]);
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
