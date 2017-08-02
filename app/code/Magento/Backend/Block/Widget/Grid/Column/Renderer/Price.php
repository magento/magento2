<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer currency
 *
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
class Price extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $_defaultWidth = 100;

    /**
     * Currency objects cache
     *
     * @var \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    protected static $_currencies = [];

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     * @since 2.0.0
     */
    protected $_localeCurrency;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_localeCurrency = $localeCurrency;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($data = $this->_getValue($row)) {
            $currencyCode = $this->_getCurrencyCode($row);

            if (!$currencyCode) {
                return $data;
            }

            $data = floatval($data) * $this->_getRate($row);
            $data = sprintf("%f", $data);
            $data = $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($data);
            return $data;
        }
        return $this->getColumn()->getDefault();
    }

    /**
     * Returns currency code for the row, false on error
     *
     * @param \Magento\Framework\DataObject $row
     * @return string|false
     * @since 2.0.0
     */
    protected function _getCurrencyCode($row)
    {
        if ($code = $this->getColumn()->getCurrencyCode()) {
            return $code;
        }
        if ($code = $row->getData($this->getColumn()->getCurrency())) {
            return $code;
        }
        return false;
    }

    /**
     * Returns rate for the row, 1 by default
     *
     * @param \Magento\Framework\DataObject $row
     * @return float|int
     * @since 2.0.0
     */
    protected function _getRate($row)
    {
        if ($rate = $this->getColumn()->getRate()) {
            return floatval($rate);
        }
        if ($rate = $row->getData($this->getColumn()->getRateField())) {
            return floatval($rate);
        }
        return 1;
    }

    /**
     * Renders CSS
     *
     * @return string
     * @since 2.0.0
     */
    public function renderCss()
    {
        return parent::renderCss() . ' col-price';
    }
}
