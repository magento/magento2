<?php
/**
 * Google AdWords conversation value type source
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_GoogleAdwords_Model_Config_Source_ValueType implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * @var Mage_GoogleAdwords_Helper_Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param Mage_GoogleAdwords_Helper_Data $helper
     */
    public function __construct(Mage_GoogleAdwords_Helper_Data $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Get conversation value type option
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_GoogleAdwords_Helper_Data::CONVERSION_VALUE_TYPE_DYNAMIC,
                'label' => $this->_helper->__('Dynamic'),
            ),
            array(
                'value' => Mage_GoogleAdwords_Helper_Data::CONVERSION_VALUE_TYPE_CONSTANT,
                'label' => $this->_helper->__('Constant'),
            ),
        );
    }
}
