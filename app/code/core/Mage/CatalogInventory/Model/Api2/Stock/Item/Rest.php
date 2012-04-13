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
 * @package     Mage_CatalogInventory
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract API2 class for stock item
 *
 * @category   Mage
 * @package    Mage_CatalogInventory
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_CatalogInventory_Model_Api2_Stock_Item_Rest
    extends Mage_CatalogInventory_Model_Api2_Stock_Item
{
    /**
     * Retrieve information about specified stock item
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $this->_loadStockItemById($this->getRequest()->getParam('id'));
        return $stockItem->getData();
    }

    /**
     * Get stock items list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = $this->_getCollectionForRetrieve()->load()->toArray();
        return isset($data['items']) ? $data['items'] : $data;
    }

    /**
     * Retrieve stock items collection
     *
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /* @var $collection Mage_CatalogInventory_Model_Resource_Stock_Item_Collection */
        $collection = Mage::getResourceModel('Mage_CatalogInventory_Model_Resource_Stock_Item_Collection');
        $this->_applyCollectionModifiers($collection);
        return $collection;
    }

    /**
     * Update specified stock item
     *
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $this->_loadStockItemById($this->getRequest()->getParam('id'));

        /* @var $validator Mage_CatalogInventory_Model_Api2_Stock_Item_Validator_Item */
        $validator = Mage::getModel('Mage_CatalogInventory_Model_Api2_Stock_Item_Validator_Item', array(
            'resource' => $this
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $stockItem->addData($data);
        try {
            $stockItem->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Update specified stock items
     *
     * @param array $data
     */
    protected function _multiUpdate(array $data)
    {
        foreach ($data as $itemData) {
            try {
                if (!is_array($itemData)) {
                    $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
                }

                /* @var $validator Mage_CatalogInventory_Model_Api2_Stock_Item_Validator_Item */
                $validator = Mage::getModel('Mage_CatalogInventory_Model_Api2_Stock_Item_Validator_Item', array(
                    'resource' => $this
                ));
                if (!$validator->isValidSingleItemDataForMultiUpdate($itemData)) {
                    foreach ($validator->getErrors() as $error) {
                        $this->_errorMessage($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST, array(
                            'item_id' => isset($itemData['item_id']) ? $itemData['item_id'] : null
                        ));
                    }
                    $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
                }

                // Existence of a item is checked in the validator
                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                $stockItem = $this->_loadStockItemById($itemData['item_id']);

                unset($itemData['item_id']); // item_id is not for update
                $stockItem->addData($itemData);
                $stockItem->save();

                $this->_successMessage(self::RESOURCE_UPDATED_SUCCESSFUL, Mage_Api2_Model_Server::HTTP_OK, array(
                    'item_id' => $stockItem->getId()
                ));
            } catch (Mage_Api2_Exception $e) {
                // pre-validation errors are already added
                if ($e->getMessage() != self::RESOURCE_DATA_PRE_VALIDATION_ERROR) {
                    $this->_errorMessage($e->getMessage(), $e->getCode(), array(
                        'item_id' => isset($itemData['item_id']) ? $itemData['item_id'] : null
                    ));
                }
            } catch (Exception $e) {
                $this->_errorMessage(
                    Mage_Api2_Model_Resource::RESOURCE_INTERNAL_ERROR,
                    Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR,
                    array(
                        'item_id' => isset($itemData['item_id']) ? $itemData['item_id'] : null
                    )
                );
            }
        }
    }
}
