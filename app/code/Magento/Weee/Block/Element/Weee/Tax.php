<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Block\Element\Weee;

class Tax extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Helper\Product $helper
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Locale\Format $localeFormat,
        array $data = []
    ) {
        $this->localeFormat = $localeFormat;
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
            if ($this->getEntityAttribute()) {
                $format= $this->localeFormat->getPriceFormat();
                $values[$key]['value'] = number_format(
                    $value['value'],
                    $format['precision'],
                    $format['decimalSymbol'],
                    $format['groupSymbol']
                );
            } else {
                // default format:  1234.56
                $values[$key]['value'] = number_format($value['value'], 2, null, '');
            }
        }

        return $values;
    }
}
