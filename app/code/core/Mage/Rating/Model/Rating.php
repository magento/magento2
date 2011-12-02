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
 * @package     Mage_Rating
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rating model
 *
 * @method Mage_Rating_Model_Resource_Rating getResource()
 * @method Mage_Rating_Model_Resource_Rating _getResource()
 * @method array getRatingCodes()
 * @method Mage_Rating_Model_Rating setRatingCodes(array $value)
 * @method array getStores()
 * @method Mage_Rating_Model_Rating setStores(array $value)
 * @method string getRatingCode()
 * @method Mage_Rating_Model_Rating getRatingCode(string $value)
 *
 * @category   Mage
 * @package    Mage_Rating
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rating_Model_Rating extends Mage_Core_Model_Abstract
{
    /**
     * rating entity codes
     *
     */
    const ENTITY_PRODUCT_CODE           = 'product';
    const ENTITY_PRODUCT_REVIEW_CODE    = 'product_review';
    const ENTITY_REVIEW_CODE            = 'review';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage_Rating_Model_Resource_Rating');
    }

    public function addOptionVote($optionId, $entityPkValue)
    {
        Mage::getModel('Mage_Rating_Model_Rating_Option')->setOptionId($optionId)
            ->setRatingId($this->getId())
            ->setReviewId($this->getReviewId())
            ->setEntityPkValue($entityPkValue)
            ->addVote();
        return $this;
    }

    public function updateOptionVote($optionId)
    {
        Mage::getModel('Mage_Rating_Model_Rating_Option')->setOptionId($optionId)
            ->setVoteId($this->getVoteId())
            ->setReviewId($this->getReviewId())
            ->setDoUpdate(1)
            ->addVote();
        return $this;
    }

    /**
     * retrieve rating options
     *
     * @return array
     */
    public function getOptions()
    {
        if ($options = $this->getData('options')) {
            return $options;
        }
        elseif ($id = $this->getId()) {
            return Mage::getResourceModel('Mage_Rating_Model_Resource_Rating_Option_Collection')
               ->addRatingFilter($id)
               ->setPositionOrder()
               ->load()
               ->getItems();
        }
        return array();
    }

    /**
     * Get rating collection object
     *
     * @return Varien_Data_Collection_Db
     */

    public function getEntitySummary($entityPkValue,  $onlyForCurrentStore = true)
    {
        $this->setEntityPkValue($entityPkValue);
        return $this->_getResource()->getEntitySummary($this, $onlyForCurrentStore);
    }

    public function getReviewSummary($reviewId,  $onlyForCurrentStore = true)
    {
        $this->setReviewId($reviewId);
        return $this->_getResource()->getReviewSummary($this, $onlyForCurrentStore);
    }

    /**
     * Get rating entity type id by code
     *
     * @param string $entityCode
     * @return int
     */
    public function getEntityIdByCode($entityCode)
    {
        return $this->getResource()->getEntityIdByCode($entityCode);
    }
}
