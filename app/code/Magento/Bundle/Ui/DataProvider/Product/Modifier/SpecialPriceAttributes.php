<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Ui\DataProvider\Product\Modifier;

use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Bundle\Model\Product\Type;
use Zend_Currency;
use Zend_Currency_Exception;

/**
 * Modify product listing price attributes
 */
class SpecialPriceAttributes implements ModifierInterface
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
     * @var DirectoryCurrency
     */
    private $directoryCurrency;

    /**
     * PriceAttributes constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param DirectoryCurrency $directoryCurrency
     * @param array $priceAttributeList
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        DirectoryCurrency $directoryCurrency,
        array $priceAttributeList = []
    ) {
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->priceAttributeList = $priceAttributeList;
        $this->directoryCurrency = $directoryCurrency;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     * @throws Zend_Currency_Exception
     */
    public function modifyData(array $data): array
    {
        if (empty($data) || empty($this->priceAttributeList)) {
            return $data;
        }

        foreach ($data['items'] as &$item) {
            foreach ($this->priceAttributeList as $priceAttribute) {
                if (isset($item[$priceAttribute]) && $item['type_id'] == Type::TYPE_CODE) {
                    $item[$priceAttribute] =
                        $this->directoryCurrency->format(
                            $item[$priceAttribute],
                            ['display' => Zend_Currency::NO_SYMBOL],
                            false
                        );
                    $item[$priceAttribute] =
                        $this->getCurrency()->toCurrency(
                            sprintf("%f", $item[$priceAttribute]),
                            ['symbol' => '%']
                        );
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
