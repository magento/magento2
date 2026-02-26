<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Eav;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor as EavIndexerProcessor;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as FlatIndexerProcessor;
use Magento\Catalog\Model\Product\ReservedAttributeList;
use Magento\Catalog\Helper\Product\Flat\Indexer as ProductFlatIndexerHelper;
use Magento\Catalog\Model\ResourceModel\Eav\SpecialToDate;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Api\Data\AttributeExtensionFactory;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\TypeFactory as EavTypeFactory;
use Magento\Eav\Model\ResourceModel\Helper as ResourceHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecialToDateTest extends TestCase
{
    /**
     * @var SpecialToDate
     */
    private SpecialToDate $specialToDate;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $registry = $this->createMock(Registry::class);
        $extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $customAttributeFactory = $this->createMock(AttributeValueFactory::class);
        $eavConfig = $this->createMock(EavConfig::class);
        $eavTypeFactory = $this->createMock(EavTypeFactory::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $resourceHelper = $this->createMock(ResourceHelper::class);
        $universalFactory = $this->createMock(UniversalFactory::class);
        $optionDataFactory = $this->createMock(AttributeOptionInterfaceFactory::class);
        $dataObjectProcessor = $this->createMock(DataObjectProcessor::class);
        $dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $localeDate = $this->createMock(TimezoneInterface::class);
        $reservedAttributeList = $this->createMock(ReservedAttributeList::class);
        $localeResolver = $this->createMock(ResolverInterface::class);
        $dateTimeFormatter = $this->createMock(DateTimeFormatterInterface::class);
        $productFlatIndexerProcessor = $this->createMock(FlatIndexerProcessor::class);
        $indexerEavProcessor = $this->createMock(EavIndexerProcessor::class);
        $productFlatIndexerHelper = $this->createMock(ProductFlatIndexerHelper::class);
        $lockValidator = $this->createMock(LockValidatorInterface::class);
        $resource = $this->createMock(Product::class);
        $resource->method('getIdFieldName')->willReturn('attribute_id');
        $resourceCollection = $this->createMock(AbstractDb::class);
        $data = [];
        $eavAttributeFactory = $this->createMock(AttributeExtensionFactory::class);

        $this->specialToDate = new SpecialToDate(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $dateTimeFormatter,
            $productFlatIndexerProcessor,
            $indexerEavProcessor,
            $productFlatIndexerHelper,
            $lockValidator,
            $resource,
            $resourceCollection,
            $data,
            $eavAttributeFactory
        );
    }

    #[DataProvider('disallowedValuesProvider')]
    public function testIsAllowedEmptyTextValueReturnsFalseForEmptyNullFalse(mixed $value): void
    {
        self::assertFalse($this->specialToDate->isAllowedEmptyTextValue($value));
    }

    /**
     * @return array
     */
    public static function disallowedValuesProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
            'false' => [false],
        ];
    }

    #[DataProvider('allowedValuesProvider')]
    public function testIsAllowedEmptyTextValueReturnsTrueForOtherValues(mixed $value): void
    {
        self::assertTrue($this->specialToDate->isAllowedEmptyTextValue($value));
    }

    /**
     * @return array
     */
    public static function allowedValuesProvider(): array
    {
        return [
            'zero int (strict check prevents treating it as false)' => [0],
            'zero string' => ['0'],
            'true' => [true],
            'space string' => [' '],
            'empty array' => [[]],
            'date string' => ['2026-02-06 00:00:00'],
        ];
    }
}
