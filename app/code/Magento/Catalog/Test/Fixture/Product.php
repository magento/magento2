<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class Product implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'id' => null,
        'type_id' => Type::TYPE_SIMPLE,
        'attribute_set_id' => 4,
        'name' => 'Simple Product%uniqid%',
        'sku' => 'simple-product%uniqid%',
        'price' => 10,
        'weight' => 1,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'custom_attributes' => [
            'tax_class_id' => '2'
        ],
        'extension_attributes' => [
            'website_ids' => [1],
            'category_links' => [],
            'stock_item' => [
                'use_config_manage_stock' => true,
                'qty' => 100,
                'is_qty_decimal' => false,
                'is_in_stock' => true,
            ]
        ],
        'product_links' => [],
        'options' => [],
        'media_gallery_entries' => [],
        'tier_prices' => [],
        'created_at' => null,
        'updated_at' => null,
    ];

    private const DEFAULT_PRODUCT_LINK_DATA = [
        'sku' => null,
        'type' => 'related',
        'position' => 1,
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataMerger
     */
    private $dataMerger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Product::DEFAULT_DATA. Custom attributes and extension attributes
     *  can be passed directly in the outer array instead of custom_attributes or extension_attributes.
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'save');

        return $service->execute(
            [
                'product' => $this->prepareData($data)
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'sku' => $data->getSku()
            ]
        );
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);
        // remove category_links if empty in order for category_ids to be processed if exists
        if (empty($data['extension_attributes']['category_links'])) {
            unset($data['extension_attributes']['category_links']);
        }

        $data['product_links'] = $this->prepareLinksData($data);

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare links data
     *
     * @param array $data
     * @return array
     */
    private function prepareLinksData(array $data): array
    {
        $links = [];

        $position = 1;
        foreach ($data['product_links'] as $link) {
            $defaultLinkData = self::DEFAULT_PRODUCT_LINK_DATA;
            $defaultLinkData['position'] = $position;
            $linkData = [];
            if (is_numeric($link)) {
                $product = $this->productRepository->getById($link);
            } elseif (is_string($link)) {
                $product = $this->productRepository->get($link);
            } elseif ($link instanceof ProductInterface) {
                $product = $this->productRepository->get($link->getSku());
            } else {
                $linkData = $link instanceof DataObject ? $link->toArray() : $link;
                $product = $this->productRepository->get($linkData['sku']);
            }

            $linkData += $defaultLinkData;
            $links[] = [
                'sku' => $data['sku'],
                'link_type' => $linkData['type'],
                'linked_product_sku' => $product->getSku(),
                'linked_product_type' =>  $product->getTypeId(),
                'position' => $linkData['position'],
                'extension_attributes' => array_diff_key($linkData, $defaultLinkData),
            ];
            $position++;
        }

        return $links;
    }
}
