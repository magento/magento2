<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Field renderer for PayPal merchant country selector
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field;

use Magento\Paypal\Model\Config\StructurePlugin;

/**
 * Class \Magento\Paypal\Block\Adminhtml\System\Config\Field\Country
 *
 */
class Country extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Config path for merchant country selector
     */
    const FIELD_CONFIG_PATH = 'paypal/general/merchant_country';

    /**
     * Request parameter name for default country
     */
    const REQUEST_PARAM_DEFAULT_COUNTRY = 'paypal_default_country';

    /**
     * Country of default scope
     *
     * @var string
     */
    protected $_defaultCountry;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Framework\View\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Url $url
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Url $url,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_url = $url;
        $this->_jsHelper = $jsHelper;
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * Render country field considering request parameter
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $country = $this->getRequest()->getParam(StructurePlugin::REQUEST_PARAM_COUNTRY);
        if ($country) {
            $element->setValue($country);
        }

        if ($element->getCanUseDefaultValue()) {
            $this->_defaultCountry = $this->_scopeConfig->getValue(self::FIELD_CONFIG_PATH);
            if (!$this->_defaultCountry) {
                $this->_defaultCountry = $this->directoryHelper->getDefaultCountry();
            }
            if ($country) {
                $shouldInherit = $country == $this->_defaultCountry
                    && $this->getRequest()->getParam(self::REQUEST_PARAM_DEFAULT_COUNTRY);
                $element->setInherit($shouldInherit);
            }
            if ($element->getInherit()) {
                $this->_defaultCountry = null;
            }
        }

        return parent::render($element);
    }

    /**
     * Get country selector html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $urlParams = [
            'section' => $this->getRequest()->getParam('section'),
            'website' => $this->getRequest()->getParam('website'),
            'store' => $this->getRequest()->getParam('store'),
            StructurePlugin::REQUEST_PARAM_COUNTRY => '__country__',
        ];
        $urlString = $this->_escaper->escapeUrl($this->_url->getUrl('*/*/*', $urlParams));
        $jsString = '
            $("' . $element->getHtmlId() . '").observe("change", function () {
                location.href = \'' . $urlString . '\'.replace("__country__", this.value);
            });
        ';

        if ($this->_defaultCountry) {
            $urlParams[self::REQUEST_PARAM_DEFAULT_COUNTRY] = '__default__';
            $urlString = $this->_escaper->escapeUrl($this->_url->getUrl('*/*/*', $urlParams));
            $jsParentCountry = $this->_escaper->escapeJs($this->_defaultCountry);
            $jsString .= '
                $("' . $element->getHtmlId() . '_inherit").observe("click", function () {
                    if (this.checked) {
                        location.href = \'' . $urlString . '\'.replace("__country__", \'' . $jsParentCountry . '\')
                            .replace("__default__", "1");
                    }
                });
            ';
        }

        return parent::_getElementHtml($element) . $this->_jsHelper->getScript(
            'require([\'prototype\'], function(){document.observe("dom:loaded", function() {' . $jsString . '});});'
        );
    }
}
