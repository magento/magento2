<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Model\Attribute\Backend\Weee;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;

/**
 * Class with fixed product taxes.
 */
class Tax extends \Magento\Catalog\Model\Product\Attribute\Backend\Price
{
    /**
     * @var \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax
     */
    protected $_attributeTax;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax $attributeTax
     * @param ScopeOverriddenValue|null $scopeOverriddenValue
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax $attributeTax,
        ?ScopeOverriddenValue $scopeOverriddenValue = null
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_storeManager = $storeManager;
        $this->_attributeTax = $attributeTax;
        parent::__construct(
            $currencyFactory,
            $storeManager,
            $catalogData,
            $config,
            $localeFormat,
            $scopeOverriddenValue
        );
    }

    /**
     * Get backend model name.
     *
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function getBackendModelName()
    {
        // phpcs:enable Magento2.Functions.StaticFunction
        return \Magento\Weee\Model\Attribute\Backend\Weee\Tax::class;
    }

    /**
     * Validate data
     *
     * @param   \Magento\Catalog\Model\Product $object
     * @return  $this
     * @throws  \Magento\Framework\Exception\LocalizedException
     */
    public function validate($object)
    {
        $taxes = $object->getData($this->getAttribute()->getName());
        if (empty($taxes)) {
            return $this;
        }
        $dup = [];
        foreach ($taxes as $tax) {
            if (!empty($tax['delete'])) {
                continue;
            }
            $state = isset($tax['state']) ? ($tax['state'] > 0 ? $tax['state'] : 0) : '0';
            $key1 = implode('-', [$tax['website_id'], $tax['country'], $state]);
            if (!empty($dup[$key1])) {
                throw new LocalizedException(
                    __(
                        'Set unique country-state combinations within the same fixed product tax. '
                        . 'Verify the combinations and try again.'
                    )
                );
            }
            $dup[$key1] = 1;
        }
        return $this;
    }

    /**
     * Assign WEEE taxes to product data
     *
     * @param   \Magento\Catalog\Model\Product $object
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterLoad($object)
    {
        $data = $this->_attributeTax->loadProductData($object, $this->getAttribute());

        foreach ($data as $i => $row) {
            if ($data[$i]['website_id'] == 0) {
                $rate = $this->_storeManager->getStore()->getBaseCurrency()->getRate(
                    $this->_directoryHelper->getBaseCurrencyCode()
                );
                if ($rate) {
                    $data[$i]['website_value'] = $data[$i]['value'] / $rate;
                } else {
                    unset($data[$i]);
                }
            } else {
                $data[$i]['website_value'] = $data[$i]['value'];
            }
        }
        $object->setData($this->getAttribute()->getName(), $data);
        return $this;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterSave($object)
    {
        $orig = $object->getOrigData($this->getAttribute()->getName());
        $current = $object->getData($this->getAttribute()->getName());
        if ($orig == $current) {
            return $this;
        }

        $this->_attributeTax->deleteProductData($object, $this->getAttribute());
        $taxes = $object->getData($this->getAttribute()->getName());

        if (!is_array($taxes)) {
            return $this;
        }

        foreach ($taxes as $tax) {
            if ((empty($tax['price']) && empty($tax['value'])) || empty($tax['country']) || !empty($tax['delete'])) {
                continue;
            }

            $state = isset($tax['state']) ? $tax['state'] : '0';

            $data = [];
            $data['website_id'] = $tax['website_id'];
            $data['country'] = $tax['country'];
            $data['state'] = $state;
            $data['value'] = !empty($tax['price']) ? $tax['price'] : $tax['value'];
            $data['attribute_id'] = $this->getAttribute()->getId();

            $this->_attributeTax->insertProductData($object, $data);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete($object)
    {
        $this->_attributeTax->deleteProductData($object, $this->getAttribute());
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTable()
    {
        return $this->_attributeTax->getTable('weee_tax');
    }

    /**
     * @inheritdoc
     */
    public function getEntityIdField()
    {
        return $this->_attributeTax->getIdFieldName();
    }
}
