<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config Directory currency backend model
 * Allows dispatching before and after events for each controller action
 */
namespace Magento\Config\Model\Config\Backend\Currency;

/**
 * @api
 * @since 100.0.2
 */
class Allow extends AbstractCurrency
{
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check is isset default display currency in allowed currencies
     * Check allowed currencies is available in installed currencies
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $exceptions = [];
        $allowedCurrencies = $this->_getAllowedCurrencies();
        foreach ($allowedCurrencies as $currencyCode) {
            if (!in_array($currencyCode, $this->_getInstalledCurrencies())) {
                $exceptions[] = __(
                    'Selected allowed currency "%1" is not available in installed currencies.',
                    $this->_localeCurrency->getCurrency($currencyCode)->getName()
                );
            }
        }

        if (!in_array($this->_getCurrencyDefault(), $allowedCurrencies)) {
            $exceptions[] = __(
                'Default display currency "%1" is not available in allowed currencies.',
                $this->_localeCurrency->getCurrency($this->_getCurrencyDefault())->getName()
            );
        }

        if ($exceptions) {
            throw new \Magento\Framework\Exception\LocalizedException(__(join("\n", $exceptions)));
        }

        return parent::afterSave();
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    protected function _getAllowedCurrencies()
    {
        $value = $this->getValue();
        $isFormData = $this->getData('groups/options/fields') !== null;
        if ($isFormData && $this->getData('groups/options/fields/allow/inherit')) {
            $value = (string)$this->_config->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
                $this->getScope(),
                $this->getScopeId()
            );
        }

        return explode(',', $value);
    }
}
