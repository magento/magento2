<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\ProductAlert\Model\StockFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class StockAlert implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'customer_id' => null,
        'product_id' => null,
        'store_id' => 1,
        'website_id' => null,
        'status' => 0,
    ];

    /**
     * @var StockFactory
     */
    private StockFactory $factory;

    /**
     * @var Stock
     */
    private Stock $resourceModel;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param StockFactory $factory
     * @param Stock $resourceModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StockFactory $factory,
        Stock $resourceModel,
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
     *      'status'        => (int) Alert Status. Optional. Default: 0.
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
