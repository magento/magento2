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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profiles listing
 */
class Mage_Sales_Block_Recurring_Profiles extends Mage_Core_Block_Template
{
    /**
     * Profiles collection
     *
     * @var Mage_Sales_Model_Resource_Recurring_Profile_Collection
     */
    protected $_profiles = null;

    /**
     * Prepare profiles collection and render it as grid information
     */
    public function prepareProfilesGrid()
    {
        $this->_prepareProfiles(array('reference_id', 'state', 'created_at', 'updated_at', 'method_code'));

        $pager = $this->getLayout()->createBlock('Mage_Page_Block_Html_Pager')
            ->setCollection($this->_profiles)->setIsOutputRequired(false);
        $this->setChild('pager', $pager);

        /* @var $profile Mage_Sales_Model_Recurring_Profile */
        $profile = Mage::getModel('Mage_Sales_Model_Recurring_Profile');

        $this->setGridColumns(array(
            new Varien_Object(array(
                'index' => 'reference_id',
                'title' => $profile->getFieldLabel('reference_id'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new Varien_Object(array(
                'index' => 'state',
                'title' => $profile->getFieldLabel('state'),
            )),
            new Varien_Object(array(
                'index' => 'created_at',
                'title' => $profile->getFieldLabel('created_at'),
                'is_nobr' => true,
                'width' => 1,
                'is_amount' => true,
            )),
            new Varien_Object(array(
                'index' => 'updated_at',
                'title' => $profile->getFieldLabel('updated_at'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new Varien_Object(array(
                'index' => 'method_code',
                'title' => $profile->getFieldLabel('method_code'),
                'is_nobr' => true,
                'width' => 1,
            )),
        ));

        $profiles = array();
        $store = Mage::app()->getStore();
        $locale = Mage::app()->getLocale();
        foreach($this->_profiles as $profile) {
            $profile->setStore($store)->setLocale($locale);
            $profiles[] = new Varien_Object(array(
                'reference_id' => $profile->getReferenceId(),
                'reference_id_link_url' => $this->getUrl('sales/recurring_profile/view/', array('profile' => $profile->getId())),
                'state'       => $profile->renderData('state'),
                'created_at'  => $this->formatDate($profile->getData('created_at'), 'medium', true),
                'updated_at'  => $profile->getData('updated_at') ? $this->formatDate($profile->getData('updated_at'), 'short', true) : '',
                'method_code' => $profile->renderData('method_code'),
            ));
        }
        if ($profiles) {
            $this->setGridElements($profiles);
        }
        $orders = array();
    }

    /**
     * Instantiate profiles collection
     *
     * @param array|int $fields
     */
    protected function _prepareProfiles($fields = '*')
    {
        $this->_profiles = Mage::getModel('Mage_Sales_Model_Recurring_Profile')->getCollection()
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId())
            ->addFieldToSelect($fields)
            ->setOrder('profile_id', 'desc')
        ;
    }

    /**
     * Set back Url
     *
     * @return Mage_Sales_Block_Recurring_Profiles
     */
    protected function _beforeToHtml()
    {
        $this->setBackUrl($this->getUrl('customer/account/'));
        return parent::_beforeToHtml();
    }
}
