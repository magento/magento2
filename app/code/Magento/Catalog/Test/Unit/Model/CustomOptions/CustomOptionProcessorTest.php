<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\CustomOptions;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;
use Magento\Catalog\Model\CustomOptions\CustomOptionProcessor;
use Magento\Catalog\Model\Product\Option\Type\File\ImageContentProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Api\Data\ProductOptionExtensionInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\ProductOption;
use Magento\Quote\Model\Quote\ProductOptionFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[CoversClass(CustomOptionProcessor::class)]
class CustomOptionProcessorTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Factory|MockObject
     */
    protected $objectFactory;

    /**
     * @var ProductOptionFactory|MockObject
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory|MockObject
     */
    protected $extensionFactory;

    /**
     * @var CustomOptionFactory|MockObject
     */
    protected $customOptionFactory;

    /** @var CartItemInterface|MockObject */
    protected $cartItem;

    /** @var ProductOptionExtensionInterface|MockObject */
    protected $extensibleAttribute;

    /** @var ProductOption|MockObject */
    protected $productOption;

    /** @var CustomOption|MockObject */
    protected $customOption;

    /** @var DataObject|MockObject */
    protected $buyRequest;

    /** @var CustomOptionProcessor */
    protected $processor;

    /** @var Json */
    private $serializer;

    protected function setUp(): void
    {
        $this->objectFactory = $this->createPartialMock(Factory::class, ['create']);
        $this->productOptionFactory = $this->createPartialMock(ProductOptionFactory::class, ['create']);
        $this->extensionFactory = $this->createPartialMock(ProductOptionExtensionFactory::class, ['create']);
        $this->customOptionFactory = $this->createPartialMock(CustomOptionFactory::class, ['create']);
        $this->cartItem = $this->createPartialMock(
            Item::class,
            ['getOptionByCode', 'getProductOption', 'setProductOption']
        );
        $this->extensibleAttribute = $this->createPartialMockWithReflection(
            ProductOptionExtensionInterface::class,
            $this->getProductOptionExtensionMethods()
        );
        $this->productOption = $this->createMock(ProductOption::class);
        $this->customOption = $this->createMock(CustomOptionInterface::class);
        $this->buyRequest = $this->createMock(DataObject::class);
        $this->serializer = $this->createMock(Json::class);

        $this->processor = new CustomOptionProcessor(
            $this->objectFactory,
            $this->productOptionFactory,
            $this->extensionFactory,
            $this->customOptionFactory,
            $this->serializer,
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(ImageContentProcessor::class)
        );
    }

    public function testConvertToBuyRequest()
    {
        $optionId = 23;
        $optionValue = 'Option value';
        $this->objectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->buyRequest);
        $this->cartItem->method('getProductOption')->willReturn($this->productOption);
        $this->productOption->method('getExtensionAttributes')->willReturn($this->extensibleAttribute);
        $this->extensibleAttribute->expects($this->atLeastOnce())
            ->method('getCustomOptions')
            ->willReturn([$this->customOption]);
        $this->customOption->expects($this->any())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->customOption->expects($this->once())
            ->method('getOptionValue')
            ->willReturn($optionValue);

        $this->assertSame($this->buyRequest, $this->processor->convertToBuyRequest($this->cartItem));
    }

    public function testProcessCustomOptions()
    {
        $optionId = 23;
        $quoteItemOption = $this->createMock(Option::class);
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getOptionByCode')
            ->with('info_buyRequest')
            ->willReturn($quoteItemOption);
        $quoteItemOption->method('getValue')->willReturn('{"options":{"' . $optionId . '":["5","6"]}}');
        $this->serializer->method('unserialize')->willReturn(json_decode($quoteItemOption->getValue(), true));
        $this->customOptionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customOption);
        $this->customOption->expects($this->once())
            ->method('setOptionId')
            ->with($optionId);
        $this->customOption->expects($this->once())
            ->method('setOptionValue')
            ->with('5,6');
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getProductOption')
            ->willReturn(false);
        $this->productOptionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->productOption);
        $this->productOption->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(false);
        $this->extensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->extensibleAttribute);
        $this->extensibleAttribute->expects($this->once())
            ->method('setCustomOptions')
            ->with([$optionId => $this->customOption]);
        $this->productOption->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensibleAttribute);
        $this->cartItem->expects($this->once())
            ->method('setProductOption')
            ->with($this->productOption);

        $this->assertSame($this->cartItem, $this->processor->processOptions($this->cartItem));
    }

    private function getProductOptionExtensionMethods(): array
    {
        return [
            'getCustomOptions',
            'setCustomOptions',
            'getBundleOptions',
            'setBundleOptions',
            'getConfigurableItemOptions',
            'setConfigurableItemOptions',
            'getDownloadableOption',
            'setDownloadableOption',
            'getGiftcardItemOption',
            'setGiftcardItemOption',
            'getGroupedOptions',
            'setGroupedOptions',
        ];
    }
}
