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
 * @package     Mage_Customer
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 class for customer (customer)
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Api2_Customer_Rest_Customer_V1 extends Mage_Customer_Model_Api2_Customer_Rest
{
    /**
     * Is customer has rights to retrieve/update customer item
     *
     * @param int $customerId
     * @throws Mage_Api2_Exception
     * @return bool
     */
    protected function _isOwner($customerId)
    {
        if ($this->getApiUser()->getUserId() !== $customerId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return true;
    }

    /**
     * Retrieve information about customer
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        if ($this->_isOwner($this->getRequest()->getParam('id'))) {
            return parent::_retrieve();
        }
    }

    /**
     * Retrieve collection with only current customer instance
     *
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        return parent::_getCollectionForRetrieve()->addAttributeToFilter('entity_id', $this->getApiUser()->getUserId());
    }

    /**
     * Update customer
     *
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        if ($this->_isOwner($this->getRequest()->getParam('id'))) {
            parent::_update($data);
        }
    }

    /**
     * Update customers
     *
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _multiUpdate(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED, Mage_Api2_Model_Server::HTTP_FORBIDDEN);
    }
}
