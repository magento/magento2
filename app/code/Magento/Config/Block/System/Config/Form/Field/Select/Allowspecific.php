<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * System configuration shipping methods allow all countries select
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Block\System\Config\Form\Field\Select;

class Allowspecific extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Add additional Javascript code
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $elementId = $this->getHtmlId();
        $countryListId = $this->_getSpecificCountryElementId();
        $useDefaultElementId = $countryListId . '_inherit';

        $elementJavaScript = <<<HTML
<script type="text/javascript">
//<![CDATA[
document.getElementById('{$elementId}').addEventListener('change', function(event) {
    var isCountrySpecific = event.target.value == 1,
        specificCountriesElement = document.getElementById('{$countryListId}'),
        // 'Use Default' checkbox of the related county list UI element
        useDefaultElement = document.getElementById('{$useDefaultElementId}');

    if (isCountrySpecific) {
        // enable related country select only if its 'Use Default' checkbox is absent or is unchecked
        specificCountriesElement.disabled = useDefaultElement ? useDefaultElement.checked : false;
    } else {
        // disable related country select if all countries are used
        specificCountriesElement.disabled = true;
    }
});
//]]>
</script>
HTML;

        return $elementJavaScript . parent::getAfterElementHtml();
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        if (!$this->getValue() || 1 != $this->getValue()) {
            $element = $this->getForm()->getElement($this->_getSpecificCountryElementId());
            $element->setDisabled('disabled');
        }
        return parent::getHtml();
    }

    /**
     * @return string
     */
    protected function _getSpecificCountryElementId()
    {
        return substr($this->getId(), 0, strrpos($this->getId(), 'allowspecific')) . 'specificcountry';
    }
}
