<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Modifier\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\Modifier\PriceAttributes as Subject;
use Magento\Framework\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Zend_Currency;
use Zend_Currency_Exception;

/**
 * Modify product listing price attributes
 */
class PriceAttributes
{
    const PRICE_ATTRIBUTE = 'special_price';
    const PRODUCT_TYPE = 'bundle';
    const PRICE_ATTRIBUTE_SYMBOL = '%';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var DirectoryCurrency
     */
    private $directoryCurrency;

    /**
     * PriceAttributes constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param DirectoryCurrency $directoryCurrency
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        DirectoryCurrency $directoryCurrency
    ) {
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->directoryCurrency = $directoryCurrency;
    }

    /**
     * @inheritdoc
     * @param Subject $subject
     * @param $result
     * @throws Zend_Currency_Exception
     * @throws NoSuchEntityException
     */
    public function afterModifyData(Subject $subject, $result)
    {
        if (empty($result)) {
            return $result;
        }

        foreach ($result['items'] as &$item) {
            if (isset($item[self::PRICE_ATTRIBUTE]) && $item['type_id'] == self::PRODUCT_TYPE) {
                $item[self::PRICE_ATTRIBUTE] = $this->directoryCurrency->format($item[self::PRICE_ATTRIBUTE], ['display' => Zend_Currency::NO_SYMBOL], false);
                $item[self::PRICE_ATTRIBUTE] = $this->getCurrency()->toCurrency(sprintf("%f", $item[self::PRICE_ATTRIBUTE]), ['symbol' => self::PRICE_ATTRIBUTE_SYMBOL]);
            }
        }

        return $result;
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
