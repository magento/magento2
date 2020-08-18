<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Price\Factory;
use Magento\Framework\Pricing\Price\Pool;

/**
 * Price models collection class.
 */
class Collection extends \Magento\Framework\Pricing\Price\Collection
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SaleableInterface $saleableItem
     * @param Factory $priceFactory
     * @param Pool $pool
     * @param float $quantity
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        SaleableInterface $saleableItem,
        Factory $priceFactory,
        Pool $pool,
        $quantity,
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($saleableItem, $priceFactory, $pool, $quantity);
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function get($code)
    {
        $customerGroupId = $this->saleableItem->getCustomerGroupId() ?? '';
        $websiteId = $this->storeManager->getStore($this->saleableItem->getStoreId())->getWebsiteId();
        $codeKey = $code . '-' . $customerGroupId . '-' . $websiteId;

        if (!isset($this->priceModels[$codeKey])) {
            $this->priceModels[$codeKey] = $this->priceFactory->create(
                $this->saleableItem,
                $this->pool[$code],
                $this->quantity
            );
        }

        return $this->priceModels[$codeKey];
    }
}
