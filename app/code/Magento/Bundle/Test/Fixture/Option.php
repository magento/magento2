<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class Option implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'option_id' => null,
        'title' => 'option%uniqid%',
        'required' => true,
        'type' => 'select',
        'position' => 1,
        'sku' => null,
        'product_links' => []
    ];

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProcessorInterface $dataProcessor
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        ProcessorInterface $dataProcessor,
        DataObjectFactory $dataObjectFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Option::DEFAULT_DATA.
     * - $data['product_links']: An array of product IDs, SKUs or instances. For advanced configuration use an array
     * like Link::DEFAULT_DATA.
     */
    public function apply(array $data = []): ?DataObject
    {
        return $this->dataObjectFactory->create(['data' => $this->prepareData($data)]);
    }

    /**
     * Prepare option data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
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

        foreach ($data['product_links'] as $link) {
            $linkData = [];
            if (is_numeric($link)) {
                $product = $this->productRepository->getById($link);
                $linkData['sku'] = $product->getSku();
            } elseif (is_string($link)) {
                $linkData['sku'] = $link;
            } elseif ($link instanceof ProductInterface) {
                $product = $this->productRepository->get($link->getSku());
                $linkData['sku'] = $product->getSku();
            } else {
                $linkData = $link instanceof DataObject ? $link->toArray() : $link;
            }

            $linkData += Link::DEFAULT_DATA;
            $links[] = $linkData;
        }

        return $links;
    }
}
