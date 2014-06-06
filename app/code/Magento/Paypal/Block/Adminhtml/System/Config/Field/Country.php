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
 * Field renderer for PayPal merchant country selector
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field;

use Magento\Paypal\Model\Config\StructurePlugin;

class Country extends \Magento\Backend\Block\System\Config\Form\Field
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
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Url $url
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Url $url,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_url = $url;
        $this->_jsHelper = $jsHelper;
        $this->_coreHelper = $coreHelper;
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
                $this->_defaultCountry = $this->_coreHelper->getDefaultCountry();
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
            StructurePlugin::REQUEST_PARAM_COUNTRY => '__country__'
        ];
        $urlString = $this->_escaper->escapeJsQuote($this->_url->getUrl('*/*/*', $urlParams));
        $jsString = '
            $("' . $element->getHtmlId() . '").observe("change", function () {
                location.href = \'' . $urlString . '\'.replace("__country__", this.value);
            });
        ';

        if ($this->_defaultCountry) {
            $urlParams[self::REQUEST_PARAM_DEFAULT_COUNTRY] = '__default__';
            $urlString = $this->_escaper->escapeJsQuote($this->_url->getUrl('*/*/*', $urlParams));
            $jsParentCountry = $this->_escaper->escapeJsQuote($this->_defaultCountry);
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
            'document.observe("dom:loaded", function() {' . $jsString . '});'
        );
    }
}
