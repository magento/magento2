<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Manage currency block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System;

class Currency extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'system/currency/rates.phtml';

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save Currency Rates'),
                'class' => 'save primary save-currency-rates',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#rate-form']],
                ]
            ]
        );

        $onClick = "setLocation('" . $this->getUrl('adminhtml/system_config/edit/section/currency') . "')";

        $this->getToolbar()->addChild(
            'options_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Options'), 'onclick' => $onClick]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Reset'), 'onclick' => 'document.location.reload()', 'class' => 'reset']
        );

        $this->addChild(
            'import_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Import'), 'class' => 'add', 'type' => 'submit']
        );

        $this->addChild('rates_matrix', \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix::class);

        $this->addChild(
            'import_services',
            \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services::class
        );

        return parent::_prepareLayout();
    }

    /**
     * Get header
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getHeader()
    {
        return __('Manage Currency Rates');
    }

    /**
     * Get save button html
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Get reset button html
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Get import button html
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getImportButtonHtml()
    {
        return $this->getChildHtml('import_button');
    }

    /**
     * Get services html
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getServicesHtml()
    {
        return $this->getChildHtml('import_services');
    }

    /**
     * Get rates matrix html
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRatesMatrixHtml()
    {
        return $this->getChildHtml('rates_matrix');
    }

    /**
     * Get import form action url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getImportFormAction()
    {
        return $this->getUrl('adminhtml/*/fetchRates');
    }
}
