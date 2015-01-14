<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Save Currency Rates'),
                'class' => 'save primary save-currency-rates',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#rate-form']],
                ]
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Reset'), 'onclick' => 'document.location.reload()', 'class' => 'reset']
        );

        $this->addChild(
            'import_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Import'), 'class' => 'add', 'type' => 'submit']
        );

        $this->addChild('rates_matrix', 'Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix');

        $this->addChild('import_services', 'Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services');

        return parent::_prepareLayout();
    }

    /**
     * Get header
     *
     * @return string
     */
    public function getHeader()
    {
        return __('Manage Currency Rates');
    }

    /**
     * Get save button html
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Get reset button html
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Get import button html
     *
     * @return string
     */
    public function getImportButtonHtml()
    {
        return $this->getChildHtml('import_button');
    }

    /**
     * Get services html
     *
     * @return string
     */
    public function getServicesHtml()
    {
        return $this->getChildHtml('import_services');
    }

    /**
     * Get rates matrix html
     *
     * @return string
     */
    public function getRatesMatrixHtml()
    {
        return $this->getChildHtml('rates_matrix');
    }

    /**
     * Get import form action url
     *
     * @return string
     */
    public function getImportFormAction()
    {
        return $this->getUrl('adminhtml/*/fetchRates');
    }
}
