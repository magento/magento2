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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect data selector form element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Form_Element_Datetime
    extends Varien_Data_Form_Element_Abstract
{
    /**
     * Date
     *
     * @var Zend_Date
     */
    protected $_value;

    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('text');
        $this->setExtType('textfield');
        if (isset($attributes['value'])) {
            $this->setValue($attributes['value']);
        }
    }

    /**
     * If script executes on x64 system, converts large
     * numeric values to timestamp limit
     *
     * @param int $value
     * @return int
     */
    protected function _toTimestamp($value)
    {
        $value = (int)$value;
        if ($value > 3155760000) {
            $value = 0;
        }
        return $value;
    }

    /**
     * Set date value
     * If Zend_Date instance is provided instead of value, other params will be ignored.
     * Format and locale must be compatible with Zend_Date
     *
     * @param mixed $value
     * @param string $format
     * @param string $locale
     * @return Varien_Data_Form_Element_Date
     */
    public function setValue($value, $format = null, $locale = null)
    {
        if (empty($value)) {
            $this->_value = '';
            return $this;
        }
        if ($value instanceof Zend_Date) {
            $this->_value = $value;
            return $this;
        }
        if (preg_match('/^[0-9]+$/', $value)) {
            $this->_value = new Zend_Date($this->_toTimestamp($value));
            //$this->_value = new Zend_Date((int)value);
            return $this;
        }
        // last check, if input format was set
        if (null === $format) {
            $format = Varien_Date::DATETIME_INTERNAL_FORMAT;
            if ($this->getInputFormat()) {
                $format = $this->getInputFormat();
            }
        }
        // last check, if locale was set
        if (null === $locale) {
            if (!$locale = $this->getLocale()) {
                $locale = null;
            }
        }
        try {
            $this->_value = new Zend_Date($value, $format, $locale);
        } catch (Exception $e) {
            $this->_value = '';
        }
        return $this;
    }

    /**
     * Get date value as string.
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with Zend_Date)
     * @return string
     */
    public function getValue($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->getFormat() . " " . $this->getFormatT();
        }
        return $this->_value->toString($format);
    }

    /**
     * Get value instance, if any
     *
     * @return Zend_Date
     */
    public function getValueInstance()
    {
        if (empty($this->_value)) {
            return null;
        }
        return $this->_value;
    }

    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (Zend_Date)
     * - output format must be set (compatible with Zend_Date)
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s style="width:110px !important;" />'
            .' <img src="%s" alt="" class="v-middle" id="%s_trig" title="%s" style="%s" />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $this->getImage(),
            $this->getHtmlId(),
            'Select Date',
            ($this->getDisabled() ? 'display:none;' : '')
        );
        $outputFormat = $this->getFormat();
        $outputTimeFormat = $this->getFormatT();
        if (empty($outputFormat)) {
            Mage::throwException(
                $this->__('Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().')
            );
        }
        $displayFormat = Varien_Date::convertZendToStrFtime($outputFormat, true, false);
        $displayTimeFormat = Varien_Date::convertZendToStrFtime($outputTimeFormat, false, true);

        $html .= sprintf('
            <script type="text/javascript">
            //<![CDATA[
                Calendar.setup({
                    inputField: "%s",
                    ifFormat: "%s",
                    showsTime: %s,
                    button: "%s_trig",
                    align: "Bl",
                    singleClick : false,
                    timeFormat: 12
                });
            //]]>
            </script>',
            $this->getHtmlId(),
            $displayFormat . " " . $displayTimeFormat,
            $this->getTime() ? 'true' : 'false',
            $this->getHtmlId()
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
