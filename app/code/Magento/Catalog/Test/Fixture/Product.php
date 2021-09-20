<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

/**
 * Creates simple product fixture
 */
class Product implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'type_id' => Type::TYPE_SIMPLE,
        'attribute_set_id' => 4,
        'name' => 'Simple Product %uniqid%',
        'sku' => 'simple_%uniqid%',
        'price' => 10,
        'weight' => 1,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'custom_attributes' => [
            'tax_class_id' => '2'
        ],
        'extension_attributes' => [
            'website_ids' => [1],
            'stock_item' => [
                'use_config_manage_stock' => true,
                'qty' => 100,
                'is_qty_decimal' => false,
                'is_in_stock' => true,
            ]
        ],
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
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'save');
        $result = $service->execute(
            [
                'product' => $this->dataProcessor->process($this, $this->prepareData($data))
            ]
        );

        return [
            'product' => $result
        ];
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'sku' => $data['product']->getSku()
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
        $default = self::DEFAULT_DATA;
        return $this->merge($default, $data);
    }

    /**
     * Recursively merge product data
     *
     * @param array $arrays
     * @return array
     */
    private function merge(array ...$arrays): array
    {
        $result = [];
        while ($arrays) {
            $array = array_shift($arrays);
            // is array an associative array
            if (array_values($array) !== $array) {
                foreach ($array as $key => $value) {
                    if (is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
                        $result[$key] = $this->merge($result[$key], $value);
                    } else {
                        $result[$key] = $value;
                    }
                }
            } elseif (array_values($result) === $result) {
                $result = $array;
            }
        }

        return $result;
    }
}
