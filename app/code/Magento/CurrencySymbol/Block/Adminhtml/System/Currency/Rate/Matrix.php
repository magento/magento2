<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Manage currency block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate;

/**
 * Manage currency block
 */
class Matrix extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_CurrencySymbol::system/currency/rate/matrix.phtml';

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_dirCurrencyFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        array $data = []
    ) {
        $this->_dirCurrencyFactory = $dirCurrencyFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $newRates = $this->_backendSession->getRates();
        $this->_backendSession->unsetData('rates');

        $currencyModel = $this->_dirCurrencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
        $oldCurrencies = $this->_prepareRates($currencyModel->getCurrencyRates($defaultCurrencies, $currencies));

        foreach ($currencies as $currency) {
            foreach ($oldCurrencies as $key => $value) {
                if (!array_key_exists($currency, $oldCurrencies[$key])) {
                    $oldCurrencies[$key][$currency] = '';
                }
            }
        }

        foreach ($oldCurrencies as $key => $value) {
            ksort($oldCurrencies[$key]);
        }

        sort($currencies);

        $this->setAllowedCurrencies(
            $currencies
        )->setDefaultCurrencies(
            $defaultCurrencies
        )->setOldRates(
            $oldCurrencies
        )->setNewRates(
            $this->_prepareRates($newRates)
        );

        return parent::_prepareLayout();
    }

    /**
     * Get rates form action
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRatesFormAction()
    {
        return $this->getUrl('adminhtml/*/saveRates');
    }

    /**
     * Prepare rates
     *
     * @param array $array
     * @return array
     */
    protected function _prepareRates($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $key => $rate) {
            foreach ($rate as $code => $value) {
                $parts = $value !== null ? explode('.', $value) : [];
                if (count($parts) == 2) {
                    $parts[1] = str_pad(rtrim($parts[1], 0), 4, '0', STR_PAD_RIGHT);
                    $array[$key][$code] = join('.', $parts);
                } elseif ($value > 0) {
                    $array[$key][$code] = number_format($value, 4);
                } else {
                    $array[$key][$code] = null;
                }
            }
        }
        return $array;
    }
}
