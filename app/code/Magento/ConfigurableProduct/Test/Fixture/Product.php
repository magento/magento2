<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class Product extends \Magento\Catalog\Test\Fixture\Product
{
    private const DEFAULT_DATA = [
        'id' => null,
        'type_id' => Configurable::TYPE_CODE,
        'attribute_set_id' => 4,
        'name' => 'Configurable Product%uniqid%',
        'sku' => 'configurable-product%uniqid%',
        'price' => null,
        'weight' => null,
        'extension_attributes' => [
            'configurable_product_options' => [],
            'configurable_product_links' => [],
        ]
    ];

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var VariationMatrix
     */
    private $variationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     * @param ProductRepositoryInterface $productRepository
     * @param Config $eavConfig
     * @param VariationMatrix $variationMatrix
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger,
        ProductRepositoryInterface $productRepository,
        Config $eavConfig,
        VariationMatrix $variationMatrix
    ) {
        parent::__construct($serviceFactory, $dataProcessor, $dataMerger, $productRepository);
        $this->eavConfig = $eavConfig;
        $this->variationMatrix = $variationMatrix;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as \Magento\Catalog\Test\Fixture\Product::DEFAULT_DATA.
     * Custom attributes and extension attributes can be passed directly in the outer array instead of custom_attributes
     * or extension_attributes.
     * Additional fields:
     *  - $data['_options']: An array of attribute IDs, codes, or instances to use as configurable product options.
     *  - $data['_links']: An array of product IDs, SKUs or instances to associate to the configurable options.
     * Products will be assigned to the variation in the same order as they are listed. Use 0 to skip a variation.
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply($this->prepareData($data));
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        if (isset($data['_options'])) {
            $productIds = [];
            $options = $this->prepareOptions($data);
            if (isset($data['_links'])) {
                $links = $this->prepareLinks($data);
                $this->associateProducts($links, $options);
                // remove holes
                $productIds = array_values(array_filter($links));
            }
            unset($data['_options'], $data['_links']);
            $data['extension_attributes']['configurable_product_options'] = $options;
            $data['extension_attributes']['configurable_product_links'] = $productIds;
        }

        return $data;
    }

    /**
     * Generate configurable options
     *
     * @param array $data
     * @return array
     */
    private function prepareOptions(array $data): array
    {
        $options = [];
        foreach ($data['_options'] as $index => $attribute) {
            $attributeId = $attribute instanceof AttributeInterface ? $attribute->getAttributeId() : $attribute;
            $attributeObject = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeId);
            $values = [];
            foreach ($attributeObject->getOptions() as $option) {
                if ($option->getValue()) {
                    $values[] = [
                        'value_index' => $option->getValue(),
                    ];
                }
            }
            $options[] = [
                'attribute_id' => $attributeObject->getId(),
                'label' => $attributeObject->getStoreLabel(),
                'position' => $index,
                'values' => $values,
            ];
        }
        return $options;
    }

    /**
     * Prepare configurable associated products
     *
     * @param array $data
     * @return array
     */
    private function prepareLinks(array $data): array
    {
        $links = [];
        foreach ($data['_links'] as $link) {
            if (!is_numeric($link)) {
                $sku = $link instanceof ProductInterface
                    ? $link->getSku()
                    : $link;
                $product = $this->productRepository->get($sku);
                $productId = $product->getId();
            } else {
                $productId = $link;
            }
            $links[] = (int) $productId;
        }

        return $links;
    }

    /**
     * Associate provided products list to configurable options
     *
     * @param array $links List of product IDs to associate to each variation in order.
     * 0 in the list means no product will be associated to the corresponding variation.
     * @param array $options
     * @return void
     */
    private function associateProducts(array $links, array $options): void
    {
        $variations = $this->variationMatrix->getVariations(
            array_map(
                static function (array $option) {
                    return [
                        'attribute_id' => $option['attribute_id'],
                        'values' => $option['values'],
                        'options' => array_map(
                            static function (array $value) {
                                return ['value' => $value['value_index']];
                            },
                            $option['values']
                        )
                    ];
                },
                $options
            )
        );
        $variationIndex = 0;
        foreach ($variations as $variation) {
            if (isset($links[$variationIndex]) && $links[$variationIndex] !== 0) {
                foreach ($variation as $attributeId => $valueInfo) {
                    $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeId);
                    $product = $this->productRepository->getById($links[$variationIndex]);
                    $product->setCustomAttribute($attribute->getAttributeCode(), $valueInfo['value']);
                    $this->productRepository->save($product);
                }
            }
            $variationIndex++;
        }
    }
}
