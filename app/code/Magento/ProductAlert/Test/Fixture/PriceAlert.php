<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\ProductAlert\Model\PriceFactory;
use Magento\ProductAlert\Model\ResourceModel\Price;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class PriceAlert implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'customer_id' => null,
        'product_id' => null,
        'store_id' => 1,
        'website_id' => null,
        'price' => 11,
    ];

    /**
     * @var PriceFactory
     */
    private PriceFactory $factory;

    /**
     * @var Price
     */
    private Price $resourceModel;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param PriceFactory $factory
     * @param Price $resourceModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PriceFactory $factory,
        Price $resourceModel,
        StoreManagerInterface $storeManager
    ) {
        $this->factory = $factory;
        $this->resourceModel = $resourceModel;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'customer_id'   => (int) Customer ID. Required.
     *      'product_id'    => (int) Product ID. Required.
     *      'store_id'      => (int) Store ID. Optional. Default: default store.
     *      'website_id'    => (int) Website ID. Optional. Default: default website.
     *      'price'         => (float) Initial Price. Optional. Default: 11.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $data['website_id'] ??= $this->storeManager->getStore($data['store_id'])->getWebsiteId();
        $model = $this->factory->create();
        $model->addData($data);
        $this->resourceModel->save($model);

        return $model;
    }
}
