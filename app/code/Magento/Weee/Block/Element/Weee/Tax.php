<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Block\Element\Weee;

use \Magento\Framework\Currency;

class Tax extends \Magento\Framework\Data\Form\Element\AbstractElement
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
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
     * @return \Magento\Store\Model\Store
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
            } catch (\Exception $e) {
                $values[$key]['value'] = $price;
            }
        }

        return $values;
    }
}
