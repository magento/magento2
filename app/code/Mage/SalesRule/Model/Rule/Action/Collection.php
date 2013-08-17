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
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_SalesRule_Model_Rule_Action_Collection extends Mage_Rule_Model_Action_Collection
{
    /**
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param array $data
     */
    public function __construct(Mage_Core_Model_View_Url $viewUrl, array $data = array())
    {
        parent::__construct($viewUrl, $data);
        $this->setType('Mage_SalesRule_Model_Rule_Action_Collection');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive($actions, array(array(
            'value' => 'Mage_SalesRule_Model_Rule_Action_Product',
            'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Update the Product'))
        ));
        return $actions;
    }
}
