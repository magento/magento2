<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Manage currency symbols block
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System;

/**
 * @api
 * @since 100.0.2
 */
class Currencysymbol extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory
     */
    protected $_symbolSystemFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolSystemFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CurrencySymbol\Model\System\CurrencysymbolFactory $symbolSystemFactory,
        array $data = []
    ) {
        $this->_symbolSystemFactory = $symbolSystemFactory;
        parent::__construct($context, $data);
    }

    /**
     * Custom currency symbol properties
     *
     * @var array
     */
    protected $_symbolsData = [];

    /**
     * Prepares layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save Currency Symbols'),
                'class' => 'save primary save-currency-symbols',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#currency-symbols-form']],
                ]
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Returns page header
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getHeader()
    {
        return __('Currency Symbols');
    }

    /**
     * Returns URL for save action
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('adminhtml/*/save');
    }

    /**
     * Returns website id
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getWebsiteId()
    {
        return $this->getRequest()->getParam('website');
    }

    /**
     * Returns store id
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }

    /**
     * Returns Custom currency symbol properties
     *
     * @return array
     */
    public function getCurrencySymbolsData()
    {
        if (!$this->_symbolsData) {
            $this->_symbolsData = $this->_symbolSystemFactory->create()->getCurrencySymbolsData();
        }
        return $this->_symbolsData;
    }

    /**
     * Returns inheritance text
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getInheritText()
    {
        return __('Use Standard');
    }
}
