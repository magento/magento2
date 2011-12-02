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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
     * Add product to Google Content.
     *
     * @param array $productIds
     * @param int $storeId
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function addProducts($productIds, $storeId)
    {
        $totalAdded = 0;
        if (is_array($productIds)) {
            foreach ($productIds as $productId) {
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
                    // Google requires CAPTCHA for login
                    $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__($e->getMessage()));
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $this->_getSession()->addError(
                        Mage::helper('Mage_GoogleShopping_Helper_Data')->parseGdataExceptionMessage($e->getMessage(), $product)
                    );
                } catch (Zend_Db_Statement_Exception $e) {
                    if ($e->getCode() == self::ERROR_CODE_SQL_UNIQUE_INDEX) {
                        $this->_getSession()->addError(
                            Mage::helper('Mage_GoogleShopping_Helper_Data')->__("The Google Content item for product '%s' (in '%s' store) has already exist.",
                                $product->getName(),
                                Mage::app()->getStore($product->getStoreId())->getName()
                            )
                        );
                    } else {
                        $this->_getSession()->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError(
                        Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The product "%s" hasn\'t been added to Google Content.', $product->getName())
                    );
                }
            }
            if (empty($productIds)) {
                return $this;
            }
        }

        if ($totalAdded > 0) {
            $this->_getSession()->addSuccess(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Total of %d product(s) have been added to Google Content.', $totalAdded)
            );
        } elseif (is_null($productIds)) {
            $this->_getSession()->addError(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Session expired during export. Please revise exported products and repeat the process if necessary.')
            );
        } else {
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__('No products were added to Google Content'));
        }

        return $this;
    }

    /**
     * Update Google Content items.
     *
     * @param array|Mage_GoogleShopping_Model_Resource_Item_Collection $items
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @return Mage_GoogleShopping_Model_MassOperations
     */
    public function synchronizeItems($items)
    {
        $totalUpdated = 0;
        $totalDeleted = 0;
        $totalFailed = 0;

        $itemsCollection = $this->_getItemsCollection($items);

        if ($itemsCollection) {
            if (count($itemsCollection) < 1) {
                return $this;
            }
            foreach ($itemsCollection as $item) {
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
                        $this->_getSession()->addError(
                            Mage::helper('Mage_GoogleShopping_Helper_Data')
                                ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct())
                        );
                        $totalFailed++;
                    }
                } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                    // Google requires CAPTCHA for login
                    $this->_getSession()->addError($e->getMessage());
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $this->_getSession()->addError(
                        Mage::helper('Mage_GoogleShopping_Helper_Data')
                            ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct())
                    );
                    $totalFailed++;
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError(
                        Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The item "%s" hasn\'t been updated.', $item->getProduct()->getName())
                    );
                    $totalFailed++;
                }
            }
        } else {
            return $this;
        }

        $this->_getSession()->addSuccess(
            Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Total of %d items(s) have been deleted; total of %d items(s) have been updated.', $totalDeleted, $totalUpdated)
        );
        if ($totalFailed > 0) {
            $this->_getSession()->addNotice(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("Cannot update %s items.", $totalFailed));
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
        if ($itemsCollection) {
            if (count($itemsCollection) < 1) {
                return $this;
            }
            foreach ($itemsCollection as $item) {
                try {
                    $item->deleteItem()->delete();
                    // The item was removed successfully
                    $totalDeleted++;
                } catch (Zend_Gdata_App_CaptchaRequiredException $e) {
                    // Google requires CAPTCHA for login
                    $this->_getSession()->addError($e->getMessage());
                    throw $e;
                } catch (Zend_Gdata_App_Exception $e) {
                    $this->_addGeneralError();
                    $this->_getSession()->addError(
                        Mage::helper('Mage_GoogleShopping_Helper_Data')
                            ->parseGdataExceptionMessage($e->getMessage(), $item->getProduct())
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError(
                        Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The item "%s" hasn\'t been deleted.', $item->getProduct()->getName())
                    );
                }
            }
        } else {
            return $this;
        }

        if ($totalDeleted > 0) {
            $this->_getSession()->addSuccess(
                Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Total of %d items(s) have been removed from Google Content.', $totalDeleted)
            );
        } else {
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__('No items were deleted from Google Content'));
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
     * Provides general error information
     */
    protected function _addGeneralError()
    {
        if (!$this->_hasError) {
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Category')->getMessage());
            $this->_hasError = true;
        }
    }
}
