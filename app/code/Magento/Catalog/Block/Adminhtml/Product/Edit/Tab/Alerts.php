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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product alerts tab
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab;

class Alerts extends \Magento\Backend\Block\Widget\Tab
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/tab/alert.phtml';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $accordion = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Accordion')->setId('productAlerts');
        /* @var $accordion \Magento\Backend\Block\Widget\Accordion */

        $alertPriceAllow = $this->_scopeConfig->getValue('catalog/productalert/allow_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $alertStockAllow = $this->_scopeConfig->getValue('catalog/productalert/allow_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($alertPriceAllow) {
            $accordion->addItem(
                'price',
                array(
                    'title' => __('We saved the price alert subscription.'),
                    'content' => $this->getLayout()->createBlock(
                        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Price'
                    )->toHtml() . '<br />',
                    'open' => true
                )
            );
        }
        if ($alertStockAllow) {
            $accordion->addItem(
                'stock',
                array(
                    'title' => __('We saved the stock notification.'),
                    'content' => $this->getLayout()->createBlock(
                        'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock'
                    ),
                    'open' => true
                )
            );
        }

        $this->setChild('accordion', $accordion);

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getAccordionHtml()
    {
        return $this->getChildHtml('accordion');
    }

    /**
     * Tab is hidden
     *
     * @return bool
     */
    public function canShowTab()
    {
        $alertPriceAllow = $this->_scopeConfig->getValue('catalog/productalert/allow_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $alertStockAllow = $this->_scopeConfig->getValue('catalog/productalert/allow_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ($alertPriceAllow || $alertStockAllow) && parent::canShowTab();
    }
}
