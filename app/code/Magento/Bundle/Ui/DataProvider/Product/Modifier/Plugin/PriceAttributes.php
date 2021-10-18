<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Modifier\Plugin;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Catalog\Ui\DataProvider\Product\Modifier\PriceAttributes as Subject;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Currency;
use Zend_Currency_Exception;

/**
 * Modify product listing price attributes
 */
class PriceAttributes
{
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
     * Added % symbol in front of special price for bundle products
     *
     * @param Subject $subject
     * @param array $result
     * @return array
     * @throws Zend_Currency_Exception
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyData(Subject $subject, array $result): array
    {
        if (empty($result)) {
            return $result;
        }

        foreach ($result['items'] as &$item) {
            if (isset($item[SpecialPrice::PRICE_CODE]) && $item['type_id'] == Type::TYPE_CODE) {
                $item[SpecialPrice::PRICE_CODE] =
                    $this->directoryCurrency->format(
                        $item[SpecialPrice::PRICE_CODE],
                        ['display' => Zend_Currency::NO_SYMBOL],
                        false
                    );
                $item[SpecialPrice::PRICE_CODE] =
                    $this->getCurrency()->toCurrency(
                        sprintf("%f", $item[SpecialPrice::PRICE_CODE]),
                        ['symbol' => '%']
                    );
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
