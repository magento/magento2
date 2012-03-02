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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tag model
 *
 * @method Mage_Tag_Model_Resource_Tag _getResource()
 * @method Mage_Tag_Model_Resource_Tag getResource()
 * @method Mage_Tag_Model_Tag setName(string $value)
 * @method int getStatus()
 * @method Mage_Tag_Model_Tag setStatus(int $value)
 * @method int getFirstCustomerId()
 * @method Mage_Tag_Model_Tag setFirstCustomerId(int $value)
 * @method int getFirstStoreId()
 * @method Mage_Tag_Model_Tag setFirstStoreId(int $value)
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Tag_Model_Tag extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLED = -1;
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    // statuses for tag relation add
    const ADD_STATUS_SUCCESS = 'success';
    const ADD_STATUS_NEW = 'new';
    const ADD_STATUS_EXIST = 'exist';
    const ADD_STATUS_REJECTED = 'rejected';

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'tag';

    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'tag';

    /**
     * This flag means should we or not add base popularity on tag load
     *
     * @var bool
     */
    protected $_addBasePopularity = false;

    protected function _construct()
    {
        $this->_init('Mage_Tag_Model_Resource_Tag');
    }

    /**
     * Init indexing process after data save
     *
     * @return Mage_Tag_Model_Tag
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        Mage::getSingleton('Mage_Index_Model_Indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $this;
    }

    /**
     * Setter for addBasePopularity flag
     *
     * @param bool $flag
     * @return Mage_Tag_Model_Tag
     */
    public function setAddBasePopularity($flag = true)
    {
        $this->_addBasePopularity = $flag;
        return $this;
    }

    /**
     * Getter for addBasePopularity flag
     *
     * @return bool
     */
    public function getAddBasePopularity()
    {
        return $this->_addBasePopularity;
    }

    /**
     * Product event tags collection getter
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_Tag_Model_Resource_Tag_Collection
     */
    protected function _getProductEventTagsCollection(Varien_Event_Observer $observer)
    {
        return $this->getResourceCollection()
                        ->joinRel()
                        ->addProductFilter($observer->getEvent()->getProduct()->getId())
                        ->addTagGroup()
                        ->load();
    }

    public function getPopularity()
    {
        return $this->_getData('popularity');
    }

    public function getName()
    {
        return $this->_getData('name');
    }

    public function getTagId()
    {
        return $this->_getData('tag_id');
    }

    public function getRatio()
    {
        return $this->_getData('ratio');
    }

    public function setRatio($ratio)
    {
        $this->setData('ratio', $ratio);
        return $this;
    }

    public function loadByName($name)
    {
        $this->_getResource()->loadByName($this, $name);
        return $this;
    }

    public function productEventAggregate($observer)
    {
        $this->_getProductEventTagsCollection($observer)->walk('aggregate');
        return $this;
    }

    /**
     * Product delete event action
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_Tag_Model_Tag
     */
    public function productDeleteEventAction($observer)
    {
        $this->_getResource()->decrementProducts($this->_getProductEventTagsCollection($observer)->getAllIds());
        return $this;
    }

    /**
     * getter for self::STATUS_APPROVED
     */
    public function getApprovedStatus()
    {
        return self::STATUS_APPROVED;
    }

    /**
     * getter for self::STATUS_PENDING
     */
    public function getPendingStatus()
    {
        return self::STATUS_PENDING;
    }

    /**
     * getter for self::STATUS_DISABLED
     */
    public function getDisabledStatus()
    {
        return self::STATUS_DISABLED;
    }

    public function getEntityCollection()
    {
        return Mage::getResourceModel('Mage_Tag_Model_Resource_Product_Collection');
    }

    public function getCustomerCollection()
    {
        return Mage::getResourceModel('Mage_Tag_Model_Resource_Customer_Collection');
    }

    public function getTaggedProductsUrl()
    {
        return Mage::getUrl('tag/product/list', array('tagId' => $this->getTagId()));
    }

    public function getViewTagUrl()
    {
        return Mage::getUrl('tag/customer/view', array('tagId' => $this->getTagId()));
    }

    public function getEditTagUrl()
    {
        return Mage::getUrl('tag/customer/edit', array('tagId' => $this->getTagId()));
    }

    public function getRemoveTagUrl()
    {
        return Mage::getUrl('tag/customer/remove', array('tagId' => $this->getTagId()));
    }

    public function getPopularCollection()
    {
        return Mage::getResourceModel('Mage_Tag_Model_Resource_Popular_Collection');
    }

    /**
     * Retrieves array of related product IDs
     *
     * @return array
     */
    public function getRelatedProductIds()
    {
        return Mage::getModel('Mage_Tag_Model_Tag_Relation')
            ->setTagId($this->getTagId())
            ->setStoreId($this->getStoreId())
            ->setStatusFilter($this->getStatusFilter())
            ->setCustomerId(null)
            ->getProductIds();
    }

    /**
     * Checks is available current tag in specified store
     *
     * @param int $storeId
     * @return bool
     */
    public function isAvailableInStore($storeId = null)
    {
        $storeId = (is_null($storeId)) ? Mage::app()->getStore()->getId() : $storeId;
        return in_array($storeId, $this->getVisibleInStoreIds());
    }

    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }

    /**
     * Save tag relation with product, customer and store
     *
     * @param int $productId
     * @param int $customerId
     * @param int $storeId
     * @return string - relation add status
     */
    public function saveRelation($productId, $customerId, $storeId)
    {
        /** @var $relationModel Mage_Tag_Model_Tag_Relation */
        $relationModel = Mage::getModel('Mage_Tag_Model_Tag_Relation');
        $relationModel->setTagId($this->getId())
            ->setStoreId($storeId)
            ->setProductId($productId)
            ->setCustomerId($customerId)
            ->setActive(Mage_Tag_Model_Tag_Relation::STATUS_ACTIVE)
            ->setCreatedAt($relationModel->getResource()->formatDate(time()));

        $relationModelSaveNeed = false;
        switch($this->getStatus()) {
            case $this->getApprovedStatus():
                if($this->_checkLinkBetweenTagProduct($relationModel)) {
                    $relation = $this->_getLinkBetweenTagCustomerProduct($relationModel);
                    if ($relation->getId()) {
                        if (!$relation->getActive()) {
                            // activate relation if it was inactive
                            $relationModel->setId($relation->getId());
                            $relationModelSaveNeed = true;
                        }
                    } else {
                        $relationModelSaveNeed = true;
                    }
                    $result = self::ADD_STATUS_EXIST;
                } else {
                    $relationModelSaveNeed = true;
                    $result = self::ADD_STATUS_SUCCESS;
                }
                break;
            case $this->getPendingStatus():
                $relation = $this->_getLinkBetweenTagCustomerProduct($relationModel);
                if ($relation->getId()) {
                    if (!$relation->getActive()) {
                        $relationModel->setId($relation->getId());
                        $relationModelSaveNeed = true;
                    }
                } else {
                    $relationModelSaveNeed = true;
                }
                $result = self::ADD_STATUS_NEW;
                break;
            case $this->getDisabledStatus():
                if($this->_checkLinkBetweenTagCustomerProduct($relationModel)) {
                    $result = self::ADD_STATUS_REJECTED;
                } else {
                    $this->setStatus($this->getPendingStatus())->save();
                    $relationModelSaveNeed = true;
                    $result = self::ADD_STATUS_NEW;
                }
                break;
        }
        if ($relationModelSaveNeed) {
            $relationModel->save();
        }

        return $result;
    }

    /**
     * Check whether product is already marked in store with tag
     *
     * @param Mage_Tag_Model_Tag_Relation $relationModel
     * @return boolean
     */
    protected function _checkLinkBetweenTagProduct($relationModel)
    {
        $customerId = $relationModel->getCustomerId();
        $relationModel->setCustomerId(null);
        $result = in_array($relationModel->getProductId(), $relationModel->getProductIds());
        $relationModel->setCustomerId($customerId);
        return $result;
    }

    /**
     * Check whether product is already marked in store with tag by customer
     *
     * @param Mage_Tag_Model_Tag_Relation $relationModel
     * @return bool
     */
    protected function _checkLinkBetweenTagCustomerProduct($relationModel)
    {
        return (count($this->_getLinkBetweenTagCustomerProduct($relationModel)->getProductIds()) > 0);
    }

    /**
     * Get relation model for product marked in store with tag by customer
     *
     * @param Mage_Tag_Model_Tag_Relation $relationModel
     * @return Mage_Tag_Model_Tag_Relation
     */
    protected function _getLinkBetweenTagCustomerProduct($relationModel)
    {
        return Mage::getModel('Mage_Tag_Model_Tag_Relation')->loadByTagCustomer(
            $relationModel->getProductId(),
            $this->getId(),
            $relationModel->getCustomerId(),
            $relationModel->getStoreId()
        );
    }

}
