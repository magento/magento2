<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Modifier;

use Magento\Framework\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
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
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * PriceAttributes constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param array $priceAttributeList
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        array $priceAttributeList = []
    ) {
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->priceAttributeList = $priceAttributeList;
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
            foreach ($this->priceAttributeList as $priceAttribute) {
                if (isset($item[$priceAttribute])) {
                    $item[$priceAttribute] = $this->getCurrency()->toCurrency(sprintf("%f", $item[$priceAttribute]));
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

    /**
     * Retrieve store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }

    /**
     * Retrieve currency
     *
     * @return Currency
     * @throws NoSuchEntityException
     */
    private function getCurrency(): Currency
    {
        $baseCurrencyCode = $this->getStore()->getBaseCurrencyCode();

        return $this->localeCurrency->getCurrency($baseCurrencyCode);
    }
}
