<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend Directory currency backend model
 * Allows dispatching before and after events for each controller action
 */
namespace Magento\Config\Model\Config\Backend\Currency;

class Base extends AbstractCurrency
{
    /** @var \Magento\Directory\Model\CurrencyFactory */
    private $currencyFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Check base currency is available in installed currencies
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $value = $this->getValue();
        if (!in_array($value, $this->_getInstalledCurrencies())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, we haven\'t installed the base currency you selected.')
            );
        }

        $this->currencyFactory->create()->saveRates([$value =>[$value => 1]]);
        return parent::afterSave();
    }
}
