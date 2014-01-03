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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GoogleCheckout\Block\Adminhtml\Shipping;

class Merchant
    extends \Magento\Backend\Block\System\Config\Form\Field
{
    protected $_addRowButtonHtml = array();
    protected $_removeRowButtonHtml = array();

    /**
     * @var \Magento\Core\Model\Website\Factory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Core\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Website\Factory $websiteFactory
     * @param \Magento\Core\Model\StoreFactory $storeFactory
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Website\Factory $websiteFactory,
        \Magento\Core\Model\StoreFactory $storeFactory,
        \Magento\Shipping\Model\Config $shippingConfig,
        array $data = array()
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->storeFactory = $storeFactory;
        $this->shippingConfig = $shippingConfig;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(\Magento\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);

        $html = '<table style="display: none;"><tbody id="merchant_allowed_methods_template">';
        $html .= $this->_getRowTemplateHtml();
        $html .= '</tbody></table>';

        $html .= '<table cellspacing="0" class="data-table">';
        $html .= '<thead><tr>';
        $html .= '<th class="col-shipping-method">' . __('Shipping Method') . '</th>';
        $html .= '<th class="col-default-price">' . __('Default Price') . '</th>';
        $html .= '<th class="col-actions">' . __('Action') . '</th>';
        $html .= '</tr></thead>';
        $html .= '<tfoot><tr><td colspan="3">';

        $html .= $this->_getAddRowButtonHtml('merchant_allowed_methods_container', 'merchant_allowed_methods_template', __('Add Shipping Method'));

        $html .= '</td></tr></tfoot>';
        $html .= '<tbody id="merchant_allowed_methods_container">';

        if ($this->_getValue('method')) {
            foreach ($this->_getValue('method') as $i => $f) {
                if ($i) {
                    $html .= $this->_getRowTemplateHtml($i);
                }
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Retrieve html template for shipping method row
     *
     * @param int $rowIndex
     * @return string
     */
    protected function _getRowTemplateHtml($rowIndex = 0)
    {
        $html = '<tr>';
        $html .= '<td class="col-shipping-method">';
        $html .= '<select name="' . $this->getElement()->getName() . '[method][]" ' . $this->_getDisabled() . '>';
        $html .= '<option value="">' . __('* Select shipping method') . '</option>';

        foreach ($this->getShippingMethods() as $carrierCode => $carrier) {
            $html .= '<optgroup label="' . $this->escapeHtml($carrier['title']) . '">';

            foreach ($carrier['methods'] as $methodCode => $method) {
                $code = $carrierCode . '/' . $methodCode;
                $html .= '<option value="' . $this->escapeHtml($code) . '" '
                    . $this->_getSelected('method/' . $rowIndex, $code)
                    . '>' . $this->escapeHtml($method['title']) . '</option>';
            }
            $html .= '</optgroup>';
        }

        $html .= '</select>';
        $html .= '</td>';
        $html .= '<td class="col-default-price">';
        $html .= '<input type="text" class="input-text" name="'
            . $this->getElement()->getName() . '[price][]" value="'
            . $this->_getValue('price/' . $rowIndex) . '" ' . $this->_getDisabled() . '/> ';
        $html .= '</td>';
        $html .= '<td class="col-actions">';
        $html .= $this->_getRemoveRowButtonHtml();
        $html .= '</td>';
        $html .= '</tr>';

        return $html;
    }

    protected function getShippingMethods()
    {
        if (!$this->hasData('shipping_methods')) {
            $website = $this->getRequest()->getParam('website');
            $store   = $this->getRequest()->getParam('store');

            $storeId = null;
            if (!is_null($website)) {
                $storeId = $this->websiteFactory->create()
                    ->load($website, 'code')
                    ->getDefaultGroup()
                    ->getDefaultStoreId();
            } elseif (!is_null($store)) {
                $storeId = $this->storeFactory->create()
                    ->load($store, 'code')
                    ->getId();
            }

            $methods = array();
            $carriers = $this->shippingConfig->getActiveCarriers($storeId);
            foreach ($carriers as $carrierCode=>$carrierModel) {
                if (!$carrierModel->isActive()) {
                    continue;
                }
                $carrierMethods = $carrierModel->getAllowedMethods();
                if (!$carrierMethods) {
                    continue;
                }
                $carrierTitle = $this->_storeConfig->getConfig('carriers/' . $carrierCode . '/title', $storeId);
                $methods[$carrierCode] = array(
                    'title'   => $carrierTitle,
                    'methods' => array(),
                );
                foreach ($carrierMethods as $methodCode=>$methodTitle) {
                    $methods[$carrierCode]['methods'][$methodCode] = array(
                        'title' => '[' . $carrierCode . '] ' . $methodTitle,
                    );
                }
            }
            $this->setData('shipping_methods', $methods);
        }
        return $this->getData('shipping_methods');
    }

    protected function _getDisabled()
    {
        return $this->getElement()->getDisabled() ? ' disabled' : '';
    }

    protected function _getValue($key)
    {
        return $this->getElement()->getData('value/' . $key);
    }

    protected function _getSelected($key, $value)
    {
        return $this->getElement()->getData('value/' . $key) == $value ? 'selected="selected"' : '';
    }

    protected function _getAddRowButtonHtml($container, $template, $title='Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setType('button')
                    ->setClass('add ' . $this->_getDisabled())
                    ->setLabel(__($title))
                    ->setOnClick("Element.insert($('" . $container . "'), {bottom: $('" . $template . "').innerHTML})")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    protected function _getRemoveRowButtonHtml($selector = 'tr', $title = 'Remove')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setType('button')
                    ->setClass('delete ' . $this->_getDisabled())
                    ->setLabel(__($title))
                    ->setOnClick("Element.remove($(this).up('" . $selector . "'))")
                    ->setDisabled($this->_getDisabled())
                    ->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }
}
