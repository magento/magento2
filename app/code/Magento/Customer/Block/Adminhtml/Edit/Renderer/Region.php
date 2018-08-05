<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Renderer;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Directory\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Customer address region field renderer
 */
class Region extends AbstractBlock implements RendererInterface
{
    /**
     * @var Data
     */
    protected $_directoryHelper;

    /**
     * @param Context $context
     * @param Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    /**
     * Output the region element and javasctipt that makes it dependent from country element
     *
     * @param AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function render(AbstractElement $element)
    {
        $country = $element->getForm()->getElement('country_id');
        if (!$country) {
            return $element->getDefaultHtml();
        }

        $regionRequired = $this->isRegionRequiredForCountryId($country->getValue());
        $regionId = $element->getForm()->getElement('region_id')->getValue();

        $html = '<div class="field field-state admin__field'. ($regionRequired ? ' required _required' : '') .'">';
        $element->setClass('input-text admin__control-text');
        $element->setRequired($regionRequired);
        $html .= $element->getLabelHtml() . '<div class="control admin__field-control">';
        $html .= $element->getElementHtml();

        $selectName = str_replace('region', 'region_id', $element->getName());
        $selectId = $element->getHtmlId() . '_id';
        $html .= '<select id="' .
            $selectId .
            '" name="' .
            $selectName .
            '" class="select admin__control-select'. ($regionRequired ? ' required-entry' : '') .'" 
            style="display:none">';
        $html .= '<option value="">' . __('Please select') . '</option>';
        $html .= '</select>';

        $html .= '<script>' . "\n";
        $html .= 'require(["prototype", "mage/adminhtml/form"], function(){';
        $html .= '$("' . $selectId . '").setAttribute("defaultValue", "' . $regionId . '");' . "\n";
        $html .= 'new regionUpdater("' .
            $country->getHtmlId() .
            '", "' .
            $element->getHtmlId() .
            '", "' .
            $selectId .
            '", ' .
            $this->_directoryHelper->getRegionJson() .
            ');' .
            "\n";

        $html .= '});';
        $html .= '</script>' . "\n";

        $html .= '</div></div>' . "\n";

        return $html;
    }

    /**
     * Whether the region is required for the current selected country
     *
     * @param string $countryId
     * @return bool
     */
    private function isRegionRequiredForCountryId(string $countryId)
    {
        return in_array($countryId, $this->_directoryHelper->getCountriesWithStatesRequired());
    }
}
