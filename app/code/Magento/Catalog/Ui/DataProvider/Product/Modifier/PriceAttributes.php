<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Modify product listing price attributes
 */
class PriceAttributes implements ModifierInterface
{
    /**
     * @var array
     */
    private $priceAttributeList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $excludeProductTypes;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * PriceAttributes constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param array $priceAttributeList
     * @param array $excludeProductTypes
     * @param PriceCurrencyInterface|null $priceCurrency
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        array $priceAttributeList = [],
        array $excludeProductTypes = [],
        ?PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->storeManager = $storeManager;
        $this->priceAttributeList = $priceAttributeList;
        $this->excludeProductTypes = $excludeProductTypes;
        $this->priceCurrency = $priceCurrency ?? ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        if (empty($data) || empty($this->priceAttributeList)) {
            return $data;
        }

        foreach ($data['items'] as &$item) {
            if (!isset($item[ProductInterface::TYPE_ID])
                || !in_array($item[ProductInterface::TYPE_ID], $this->excludeProductTypes, true)
            ) {
                foreach ($this->priceAttributeList as $priceAttribute) {
                    if (isset($item[$priceAttribute])) {
                        $item[$priceAttribute] = $this->priceCurrency->format(
                            sprintf("%F", $item[$priceAttribute]),
                            false,
                            PriceCurrencyInterface::DEFAULT_PRECISION,
                            $this->storeManager->getStore($item['store_id'] ?? null)
                        );
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
