<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

class Product extends \Magento\Catalog\Test\Fixture\Product
{
    private const DEFAULT_DATA = [
        'type_id' => Grouped::TYPE_CODE,
        'name' => 'Grouped Product%uniqid%',
        'sku' => 'grouped-product%uniqid%',
        'price' => null,
        'weight' => null,
        'product_links' => [],
    ];

    private const DEFAULT_PRODUCT_LINK_DATA = [
        'sku' => null,
        'type' => 'associated',
        'position' => 1,
        'qty' => 1,
    ];

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($serviceFactory, $dataProcessor, $dataMerger, $productRepository);
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply($this->prepareData($data));
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as \Magento\Catalog\Test\Fixture\Product::DEFAULT_DATA.
     * Custom attributes and extension attributes can be passed directly in the outer array instead of custom_attributes
     * or extension_attributes.
     *  - $data['product_links']: An array of product IDs, SKUs or instances to associate to the grouped product. For
     * advanced configuration, use an array{sku: string, position: int, qty: int}
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $data['product_links'] = $this->prepareLinksData($data);

        return $data;
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
            $linkData = [];
            $defaultLinkData = self::DEFAULT_PRODUCT_LINK_DATA;
            $defaultLinkData['position'] = $position;
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
            if (isset($link['type']) && $link['type'] !== $defaultLinkData['type']) {
                unset($defaultLinkData['qty']);
            }
            $linkData['sku'] = $product->getSku();
            $linkData += $defaultLinkData;
            $links[] = $linkData;
            $position++;
        }

        return $links;
    }
}
