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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product review form xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Review_Form extends Mage_Core_Block_Template
{
    /**
     * Collection of ratings
     *
     * @var array
     */
    protected $_ratings = null;

    /**
     * Render product review form xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();
        /** @var $xmlReview Mage_XmlConnect_Model_Simplexml_Element */
        $xmlReview = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', array('data' => '<form></form>'));
        $xmlReview->addAttribute('name', 'review_form');
        $xmlReview->addAttribute('method', 'post');

        $nickname = '';
        if ($customer->getId()) {
            $nickname = $xmlReview->escapeXml($customer->getFirstname());
        }

        if ($this->getRatings()) {
            $ratingsFieldset = $xmlReview->addCustomChild('fieldset', null, array(
                'label' => $this->__('How do you rate this product?')
            ));

            foreach ($this->getRatings() as $rating) {
                $ratingField = $ratingsFieldset->addField('ratings[' . $rating->getId() . ']', 'radio', array(
                    'label'     => $rating->getRatingCode(),
                    'required'  => 'true'
                ));
                foreach ($rating->getOptions() as $option) {
                    $ratingField->addCustomChild('value', $option->getId());
                }
            }
        }

        $reviewFieldset = $xmlReview->addCustomChild('fieldset');
        $reviewFieldset->addField('nickname', 'text', array(
            'label'     => $this->__('Nickname'),
            'required'  => 'true',
            'value'     => $nickname
        ));
        $reviewFieldset->addField('title', 'text', array(
            'label'     => $this->__('Summary of Your Review'),
            'required'  => 'true'
        ));
        $reviewFieldset->addField('detail', 'textarea', array(
            'label'     => $this->__('Review'),
            'required'  => 'true'
        ));

        return $xmlReview->asNiceXml();
    }

    /**
     * Returns collection of ratings
     *
     * @return array | false
     */
    public function getRatings()
    {
        if (is_null($this->_ratings)) {
            $this->_ratings = Mage::getModel('Mage_Rating_Model_Rating')->getResourceCollection()->addEntityFilter('product')
                ->setPositionOrder()->addRatingPerStoreName(Mage::app()->getStore()->getId())
                ->setStoreFilter(Mage::app()->getStore()->getId())->load()->addOptionToItems();

            if (!$this->_ratings->getSize()) {
                $this->_ratings = false;
            }
        }
        return $this->_ratings;
    }
}
