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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controller for mass opertions with items
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_MassOperations
{
    /**
     * Zend_Db_Statement_Exception code for "Duplicate unique index" error
     *
     * @var int
     */
    const ERROR_CODE_SQL_UNIQUE_INDEX = 23000;

    /**
     * Whether general error information were added
     *
     * @var bool
     */
    protected $_hasError = false;

    /**
     * Process locking flag
     *
     * @var Mage_GoogleShopping_Model_Flag
     */
    protected $_flag;

    /**
     * Set process locking flag.
     *
     * @param Mage_GoogleShopping_Model_Flag $flag
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function setFlag(Mage_GoogleShopping_Model_Flag $flag)
    {
        $this->_flag = $flag;
        return $this;
    }

    /**
     * Add product to Google Content.
     *
     * @param array $productIds
     * @param int $storeId
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @throws Mage_Core_Exception
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function addProducts($productIds, $storeId)
    {
        $totalAdded = 0;
        $errors = array();
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $product = Mage::getModel('Mage_Catalog_Model_Product')
                        ->setStoreId($storeId)
                        ->load($productId);

                    if ($product->getId()) {
                        Mage::getModel('Mage_GoogleShopping_Model_Item')
                            ->insertItem($product)
                            ->save();
                        // The product was added successfully
                        $totalAdded++;
                    }
                } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')->parseGdataExceptionMessage($e->getMessage(), $product);
                } catch (Zend_Db_Statement_Exception $e) {
                    $message = $e->getMessage();
                    if ($e->getCode() == self::ERROR_CODE_SQL_UNIQUE_INDEX) {
                        $message = Mage::helper('Mage_GoogleShopping_Helper_Data')->__("The Google Content item for product '%s' (in '%s' store) has already exist.", $product->getName(), Mage::app()->getStore($product->getStoreId())->getName());
                    }
                    $errors[] = $message;
                } catch (Mage_Core_Exception $e) {
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The product "%s" cannot be added to Google Content. %s', $product->getName(), $e->getMessage());
                } catch (Exception $e) {
                    Mage::logException($e);
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The product "%s" hasn\'t been added to Google Content.', $product->getName());
                }
            }
            if (empty($productIds)) {
                return $this;
            }
        }

        if ($totalAdded > 0) {
            $this->_getNotifier()->addNotice(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Products were added to Google Shopping account.'),
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Total of %d product(s) have been added to Google Content.', $totalAdded)
            );
        }

        if (count($errors)) {
            $this->_getNotifier()->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Errors happened while adding products to Google Shopping.'),
                $errors
            );
        }

        if ($this->_flag->isExpired()) {
            $this->_getNotifier()->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Operation of adding products to Google Shopping expired.'),
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Some products may have not been added to Google Shopping bacause of expiration')
            );
        }

        return $this;
    }

    /**
     * Update Google Content items.
     *
     * @param array|Mage_GoogleShopping_Model_Resource_Item_Collection $items
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @throws Mage_Core_Exception
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function synchronizeItems($items)
    {
        $totalUpdated = 0;
        $totalDeleted = 0;
        $totalFailed = 0;
        $errors = array();

        $itemsCollection = $this->_getItemsCollection($items);

        if ($itemsCollection) {
            if (count($itemsCollection) < 1) {
                return $this;
            }
            foreach ($itemsCollection as $item) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $item->updateItem();
                    $item->save();
                    // The item was updated successfully
                    $totalUpdated++;
                } catch (Varien_Gdata_Gshopping_HttpException $e) {
                    if (in_array('notfound', $e->getCodes())) {
                        $item->delete();
                        $totalDeleted++;
                    } else {
                        $this->_addGeneralError();
                        $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')
                            ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
                        $totalFailed++;
                    }
                } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')
                        ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
                    $totalFailed++;
                } catch (Mage_Core_Exception $e) {
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The item "%s" cannot be updated at Google Content. %s', $item->getProduct()->getName(), $e->getMessage());
                    $totalFailed++;
                } catch (Exception $e) {
                    Mage::logException($e);
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The item "%s" hasn\'t been updated.', $item->getProduct()->getName());
                    $totalFailed++;
                }
            }
        } else {
            return $this;
        }

        $this->_getNotifier()->addNotice(
            Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Product synchronization with Google Shopping completed'),
            Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Total of %d items(s) have been deleted; total of %d items(s) have been updated.', $totalDeleted, $totalUpdated)
        );
        if ($totalFailed > 0 || count($errors)) {
            array_unshift($errors, Mage::helper('Mage_GoogleShopping_Helper_Data')->__("Cannot update %s items.", $totalFailed));
            $this->_getNotifier()->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Errors happened during synchronization with Google Shopping'),
                $errors
            );
        }

        return $this;
    }

    /**
     * Remove Google Content items.
     *
     * @param array|Mage_GoogleShopping_Model_Resource_Item_Collection $items
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function deleteItems($items)
    {
        $totalDeleted = 0;
        $itemsCollection = $this->_getItemsCollection($items);
        $errors = array();
        if ($itemsCollection) {
            if (count($itemsCollection) < 1) {
                return $this;
            }
            foreach ($itemsCollection as $item) {
                if ($this->_flag && $this->_flag->isExpired()) {
                    break;
                }
                try {
                    $item->deleteItem()->delete();
                    // The item was removed successfully
                    $totalDeleted++;
                } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')
                        ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct());
                } catch (Exception $e) {
                    Mage::logException($e);
                    $errors[] = Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The item "%s" hasn\'t been deleted.', $item->getProduct()->getName());
                }
            }
        } else {
            return $this;
        }

        if ($totalDeleted > 0) {
            $this->_getNotifier()->addNotice(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Google Shopping item removal process succeded'),
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Total of %d items(s) have been removed from Google Shopping.', $totalDeleted)
            );
        }
        if (count($errors)) {
            $this->_getNotifier()->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Errors happened while deleting items from Google Shopping'),
                $errors
            );
        }

        return $this;
    }

    /**
     * Return items collection by IDs
     *
     * @param array|Mage_GoogleShopping_Model_Resource_Item_Collection $items
     * @throws Mage_Core_Exception
     * @return null|Mage_GoogleShopping_Model_Resource_Item_Collection
     */
    protected function _getItemsCollection($items)
    {
        $itemsCollection = null;
        if ($items instanceof Mage_GoogleShopping_Model_Resource_Item_Collection) {
            $itemsCollection = $items;
        } else if (is_array($items)) {
            $itemsCollection = Mage::getResourceModel('Mage_GoogleShopping_Model_Resource_Item_Collection')
                ->addFieldToFilter('item_id', $items);
        }

        return $itemsCollection;
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session');
    }

    /**
     * Retrieve admin notifier
     *
     * @return Mage_Adminhtml_Model_Inbox
     */
    protected function _getNotifier()
    {
        return Mage::getModel('Mage_AdminNotification_Model_Inbox');
    }

    /**
     * Provides general error information
     */
    protected function _addGeneralError()
    {
        if (!$this->_hasError) {
            $this->_getNotifier()->addMajor(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Google Shopping Error'),
                Mage::helper('Mage_GoogleShopping_Helper_Category')->getMessage()
            );
            $this->_hasError = true;
        }
    }
}
