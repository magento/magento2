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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source model for eav attribute custom_design
 */
class Mage_Core_Model_Design_Source_Design extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Theme_Label
     */
    protected $_themeLabel;

    /**
     * @param Mage_Core_Helper_Data $helper
     * @param Mage_Core_Model_Theme_Label $themeLabel
     */
    public function __construct(Mage_Core_Helper_Data $helper, Mage_Core_Model_Theme_Label $themeLabel)
    {
        $this->_helper = $helper;
        $this->_themeLabel = $themeLabel;
    }

    /**
     * Retrieve All Design Theme Options
     *
     * @param bool $withEmpty add empty (please select) values to result
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        $label = $withEmpty ? $this->_helper->__('-- Please Select --') : $withEmpty;
        return $this->_options = $this->_themeLabel->getLabelsCollection($label);
    }
}
