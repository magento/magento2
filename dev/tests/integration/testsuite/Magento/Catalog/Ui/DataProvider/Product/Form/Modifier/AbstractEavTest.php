<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav\CompositeConfigProcessor;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\DataProvider\Mapper\FormElement;
use Magento\Ui\DataProvider\Mapper\MetaProperties;
use PHPUnit\Framework\TestCase;

/**
 * Base class for eav modifier tests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractEavTest extends TestCase
{
    /**
     * @var Eav
     */
    protected $eavModifier;

    /**
     * @var LocatorInterface|PHPUnit\Framework\MockObject\MockObject
     */
    protected $locatorMock;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var int
     */
    protected $defaultSetId;

    /**
     * @var MetaProperties
     */
    private $metaPropertiesMapper;

    /**
     * @var CompositeConfigProcessor
     */
    private $compositeConfigProcessor;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $mappings = [
            'text' => 'input',
            'hidden' => 'input',
            'boolean' => 'checkbox',
            'media_image' => 'image',
            'price' => 'input',
            'weight' => 'input',
            'gallery' => 'image'
        ];
        $this->objectManager = Bootstrap::getObjectManager();
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->locatorMock->expects($this->any())->method('getStore')->willReturn(
            $this->objectManager->get(StoreInterface::class)
        );
        $this->metaPropertiesMapper = $this->objectManager->create(MetaProperties::class, ['mappings' => []]);
        $this->compositeConfigProcessor = $this->objectManager->create(
            CompositeConfigProcessor::class,
            ['eavWysiwygDataProcessors' => []]
        );
        $this->eavModifier = $this->objectManager->create(
            Eav::class,
            [
                'locator' => $this->locatorMock,
                'formElementMapper' => $this->objectManager->create(FormElement::class, ['mappings' => $mappings]),
                'metaPropertiesMapper' => $this->metaPropertiesMapper,
                'wysiwygConfigProcessor' => $this->compositeConfigProcessor,
            ]
        );
        $this->attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productFactory = $this->objectManager->get(ProductInterfaceFactory::class);
        $this->defaultSetId = (int)$this->objectManager->create(Type::class)
            ->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();
    }

    /**
     * @param ProductInterface $product
     * @param array $expectedMeta
     * @return void
     */
    protected function callModifyMetaAndAssert(ProductInterface $product, array $expectedMeta): void
    {
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($product);
        $actualMeta = $this->eavModifier->modifyMeta([]);
        $this->prepareDataForComparison($actualMeta, $expectedMeta);
        $this->assertEquals($expectedMeta, $actualMeta);
    }

    /**
     * @param ProductInterface $product
     * @param array $expectedData
     * @return void
     */
    protected function callModifyDataAndAssert(ProductInterface $product, array $expectedData): void
    {
        $this->locatorMock->expects($this->any())->method('getProduct')->willReturn($product);
        $actualData = $this->eavModifier->modifyData([]);
        $this->prepareDataForComparison($actualData, $expectedData);
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * Prepare data for comparison to avoid false positive failures.
     *
     * Make sure that $data contains all the data contained in $expectedData,
     * ignore all fields not declared in $expectedData
     *
     * @param array &$data
     * @param array $expectedData
     * @return void
     */
    protected function prepareDataForComparison(array &$data, array $expectedData): void
    {
        foreach ($data as $key => &$item) {
            if (!isset($expectedData[$key])) {
                unset($data[$key]);
                continue;
            }
            if ($item instanceof Phrase) {
                $item = (string)$item;
            } elseif (is_array($item)) {
                $this->prepareDataForComparison($item, $expectedData[$key]);
            } elseif ($key === 'price_id' || $key === 'sortOrder') {
                $data[$key] = '__placeholder__';
            }
        }
    }

    /**
     * Updates attribute default value.
     *
     * @param string $attributeCode
     * @param string $defaultValue
     * @return void
     */
    protected function setAttributeDefaultValue(string $attributeCode, string $defaultValue): void
    {
        $attribute = $this->attributeRepository->get($attributeCode);
        $attribute->setDefaultValue($defaultValue);
        $this->attributeRepository->save($attribute);
    }

    /**
     * Returns attribute options list.
     *
     * @param string $attributeCode
     * @return array
     */
    protected function getAttributeOptions(string $attributeCode): array
    {
        $attribute = $this->attributeRepository->get($attributeCode);

        return $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : [];
    }

    /**
     * Returns attribute option value by id.
     *
     * @param string $attributeCode
     * @param string $label
     * @return int|null
     */
    protected function getOptionValueByLabel(string $attributeCode, string $label): ?int
    {
        $result = null;
        foreach ($this->getAttributeOptions($attributeCode) as $option) {
            if ($option['label'] == $label) {
                $result = (int)$option['value'];
            }
        }

        return $result;
    }

    /**
     * Returns product for testing.
     *
     * @return ProductInterface
     */
    protected function getProduct(): ProductInterface
    {
        return $this->productRepository->get('simple', false, Store::DEFAULT_STORE_ID);
    }

    /**
     * Returns new product object.
     *
     * @return ProductInterface
     */
    protected function getNewProduct(): ProductInterface
    {
        $product = $this->productFactory->create();
        $product->setAttributeSetId($this->defaultSetId);

        return $product;
    }

    /**
     * Updates product.
     *
     * @param ProductInterface $product
     * @param array $attributeData
     * @return void
     */
    protected function saveProduct(ProductInterface $product, array $attributeData): void
    {
        $product->addData($attributeData);
        $this->productRepository->save($product);
    }

    /**
     * Adds additional array nesting to expected meta.
     *
     * @param array $attributeMeta
     * @param string $groupCode
     * @param string $attributeCode
     * @return array
     */
    protected function addMetaNesting(array $attributeMeta, string $groupCode, string $attributeCode): array
    {
        return [
            $groupCode => [
                'arguments' => ['data' => ['config' => ['dataScope' => 'data.product']]],
                'children' => [
                    'container_' . $attributeCode => [
                        'children' => [$attributeCode => ['arguments' => ['data' => ['config' => $attributeMeta]]]],
                    ],
                ],
            ],
        ];
    }

    /**
     * Adds additional array nesting to expected data.
     *
     * @param array $data
     * @return array
     */
    protected function addDataNesting(array $data): array
    {
        return [1 => ['product' => $data]];
    }

    /**
     * Returns attribute codes from product meta data array.
     *
     * @param array $actualMeta
     * @return array
     */
    protected function getUsedAttributes(array $actualMeta): array
    {
        $usedAttributes = [];
        foreach ($actualMeta as $group) {
            foreach (array_keys($group['children']) as $field) {
                $usedAttributes[] = str_replace('container_', '', $field);
            }
        }

        return $usedAttributes;
    }
}
