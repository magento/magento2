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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * REgion field renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Model_Customer_Renderer_Region implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Country region collections
     *
     * array(
     *      [$countryId] => Varien_Data_Collection_Db
     * )
     *
     * @var array
     */
    static protected $_regionCollections;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr>'."\n";

        $countryId = false;
        if ($country = $element->getForm()->getElement('country_id')) {
            $countryId = $country->getValue();
        }

        $regionCollection = false;
        if ($countryId) {
            if (!isset(self::$_regionCollections[$countryId])) {
                self::$_regionCollections[$countryId] = Mage::getModel('Mage_Directory_Model_Country')
                    ->setId($countryId)
                    ->getLoadedRegionCollection()
                    ->toOptionArray();
            }
            $regionCollection = self::$_regionCollections[$countryId];
        }

        $regionId = intval($element->getForm()->getElement('region_id')->getValue());

        $htmlAttributes = $element->getHtmlAttributes();
        foreach ($htmlAttributes as $key => $attribute) {
            if ('type' === $attribute) {
                unset($htmlAttributes[$key]);
                break;
            }
        }

        // Output two elements - for 'region' and for 'region_id'.
        // Two elements are needed later upon form post - to properly set data to address model,
        // otherwise old value can be left in region_id attribute and saved to DB.
        // Depending on country selected either 'region' (input text) or 'region_id' (selectbox) is visible to user
        $regionHtmlName = $element->getName();
        $regionIdHtmlName = str_replace('region', 'region_id', $regionHtmlName);
        $regionHtmlId = $element->getHtmlId();
        $regionIdHtmlId = str_replace('region', 'region_id', $regionHtmlId);

        if ($regionCollection && count($regionCollection) > 0) {
            $elementClass = $element->getClass();
            $html.= '<td class="label">'.$element->getLabelHtml().'</td>';
            $html.= '<td class="value">';

            $html .= '<select id="' . $regionIdHtmlId . '" name="' . $regionIdHtmlName . '" '
                 . $element->serialize($htmlAttributes) .'>' . "\n";
            foreach ($regionCollection as $region) {
                $selected = ($regionId==$region['value']) ? ' selected="selected"' : '';
                $html.= '<option value="'.(int)$region['value'].'"'.$selected.'>'.$region['label'].'</option>';
            }
            $html.= '</select>' . "\n";

            $html .= '<input type="hidden" name="' . $regionHtmlName . '" id="' . $regionHtmlId . '" value=""/>';

            $html.= '</td>';
            $element->setClass($elementClass);
        } else {
            $element->setClass('input-text');
            $html.= '<td class="label"><label for="'.$element->getHtmlId().'">'
                . $element->getLabel()
                . ' <span class="required" style="display:none">*</span></label></td>';

            $element->setRequired(false);
            $html.= '<td class="value">';
            $html .= '<input id="' . $regionHtmlId . '" name="' . $regionHtmlName
                 . '" value="' . $element->getEscapedValue() . '" ' . $element->serialize($htmlAttributes) . "/>" . "\n";
            $html .= '<input type="hidden" name="' . $regionIdHtmlName . '" id="' . $regionIdHtmlId . '" value=""/>';
            $html .= '</td>'."\n";
        }
        $html.= '</tr>'."\n";
        return $html;
    }
}
