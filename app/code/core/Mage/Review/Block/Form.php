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
 * @package     Mage_Review
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Review_Block_Form extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $customerSession = Mage::getSingleton('Mage_Customer_Model_Session');

        parent::_construct();

        $data = Mage::getSingleton('Mage_Review_Model_Session')->getFormData(true);
        $data = new Varien_Object((array)$data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = $customerSession->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }

        $this->setAllowWriteReviewFlag(
            $customerSession->isLoggedIn() || Mage::helper('Mage_Review_Helper_Data')->getIsGuestAllowToWrite()
        );
        if (!$this->getAllowWriteReviewFlag()) {
            $queryParam = Mage::helper('Mage_Core_Helper_Data')->urlEncode(
                Mage::getUrl('*/*/*', array('_current' => true)) .
                '#review-form'
            );
            $this->setLoginLink(Mage::getUrl(
                    'customer/account/login/',
                    array(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME => $queryParam)
                )
            );
        }

        $this->setTemplate('form.phtml')
            ->assign('data', $data)
            ->assign('messages', Mage::getSingleton('Mage_Review_Model_Session')->getMessages(true));
    }

    public function getProductInfo()
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        return $product->load($this->getRequest()->getParam('id'));
    }

    public function getAction()
    {
        $productId = Mage::app()->getRequest()->getParam('id', false);
        return Mage::getUrl('review/product/post', array('id' => $productId));
    }

    public function getRatings()
    {
        $ratingCollection = Mage::getModel('Mage_Rating_Model_Rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->setActiveFilter(true)
            ->load()
            ->addOptionToItems();
        return $ratingCollection;
    }
}
