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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * EAV Entity Attribute Multiply select Data Model
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Attribute_Data_Multiselect extends Mage_Eav_Model_Attribute_Data_Select
{
    /**
     * Extract data from request and return value
     *
     * @param Zend_Controller_Request_Http $request
     * @return array|string
     */
    public function extractValue(Zend_Controller_Request_Http $request)
    {
        $values = $this->_getRequestValue($request);
        if ($values !== false && !is_array($values)) {
            $values = array($values);
        }
        return $values;
    }

    /**
     * Export attribute value to entity model
     *
     * @param array|string $value
     * @return Mage_Eav_Model_Attribute_Data_Multiselect
     */
    public function compactValue($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        return parent::compactValue($value);
    }

    /**
     * Return formated attribute value from entity model
     *
     * @return string|array
     */
    public function outputValue($format = Mage_Eav_Model_Attribute_Data::OUTPUT_FORMAT_TEXT)
    {
        $values = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        switch ($format) {
            case Mage_Eav_Model_Attribute_Data::OUTPUT_FORMAT_JSON:
            case Mage_Eav_Model_Attribute_Data::OUTPUT_FORMAT_ARRAY:
                $output = $values;
            default:
                $output = array();
                foreach ($values as $value) {
                    if (!$value) {
                        continue;
                    }
                    $output[] = $this->getAttribute()->getSource()->getOptionText($value);
                }
                $output = implode(', ', $output);
                break;
        }

        return $output;
    }
}
