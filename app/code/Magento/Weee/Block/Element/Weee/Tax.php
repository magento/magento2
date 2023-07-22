<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Block\Element\Weee;

use Exception;
use \Magento\Framework\Currency;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Tax extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        protected StoreManagerInterface $storeManager,
        protected CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @param mixed $attribute
     * @return Store
     */
    protected function getStore($attribute)
    {
        if (!($storeId = $attribute->getStoreId())) {
            $storeId = $this->getForm()->getDataObject()->getStoreId();
        }
        $store = $this->storeManager->getStore($storeId);
        return $store;
    }

    /**
     * @param null|int|string $index
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getEscapedValue($index = null)
    {
        $values = $this->getValue();

        if (!is_array($values)) {
            return null;
        }

        foreach ($values as $key => $value) {
            $price = array_key_exists('price', $value) ? $value['price'] : $value['value'];
            try {
                if ($attribute = $this->getEntityAttribute()) {
                    $store = $this->getStore($attribute);
                    $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

                    $values[$key]['value'] = $currency->toCurrency($price, ['display' => Currency::NO_SYMBOL]);
                } else {
                    // default format:  1234.56
                    $values[$key]['value'] = number_format($price, 2, null, '');
                }
            } catch (Exception $e) {
                $values[$key]['value'] = $price;
            }
        }

        return $values;
    }
}
