<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Manage currency block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System;

class Currency extends \Magento\Backend\Block\Template
{
    protected $_template = 'system/currency/rates.phtml';

    protected function _prepareLayout()
    {
        $this->addChild('save_button', 'Magento\Adminhtml\Block\Widget\Button', array(
                'label' => __('Save Currency Rates'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array('event' => 'save', 'target' => '#rate-form'),
        ))));

        $this->addChild('reset_button', 'Magento\Adminhtml\Block\Widget\Button', array(
                'label' => __('Reset'),
                'onclick' => 'document.location.reload()',
                'class' => 'reset'
        ));

        $this->addChild('import_button', 'Magento\Adminhtml\Block\Widget\Button', array(
                'label' => __('Import'),
                'class' => 'add',
                'type' => 'submit',
        ));

        $this->addChild('rates_matrix', 'Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix');

        $this->addChild('import_services', 'Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services');

        return parent::_prepareLayout();
    }

    public function getHeader()
    {
        return __('Manage Currency Rates');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getImportButtonHtml()
    {
        return $this->getChildHtml('import_button');
    }

    public function getServicesHtml()
    {
        return $this->getChildHtml('import_services');
    }

    public function getRatesMatrixHtml()
    {
        return $this->getChildHtml('rates_matrix');
    }

    public function getImportFormAction()
    {
        return $this->getUrl('*/*/fetchRates');
    }

}
