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
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract API2 class for product website resource
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Model_Api2_Product_Website_Rest extends Mage_Catalog_Model_Api2_Product_Website
{
    /**
     * Product website retrieve is not available
     */
    protected function _retrieve()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

    /**
     * Get product websites list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $return = array();
        foreach ($this->_loadProductById($this->getRequest()->getParam('product_id'))->getWebsiteIds() as $websiteId) {
            $return[] = array('website_id' => $websiteId);
        }
        return $return;
    }

    /**
     * Product website assign
     *
     * @param array $data
     * @return string
     */
    protected function _create(array $data)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->_loadProductById($this->getRequest()->getParam('product_id'));

        /* @var $validator Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website */
        $validator = Mage::getModel('Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website');
        if (!$validator->isValidDataForWebsiteAssignmentToProduct($product, $data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $websiteIds = $product->getWebsiteIds();
        /* @var $website Mage_Core_Model_Website */
        $website = Mage::getModel('Mage_Core_Model_Website')->load($data['website_id']);
        $websiteIds[] = $website->getId(); // Existence of a website is checked in the validator
        $product->setWebsiteIds($websiteIds);

        try{
            $product->save();

            /**
             * Do copying data to stores
             */
            if (isset($data['copy_to_stores'])) {
                foreach ($data['copy_to_stores'] as $storeData) {
                    Mage::getModel('Mage_Catalog_Model_Product')
                        ->setStoreId($storeData['store_from'])
                        ->load($product->getId())
                        ->setStoreId($storeData['store_to'])
                        ->save();
                }
            }

        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        return $this->_getLocation($website, $product);
    }

    /**
     * Product website assign
     *
     * @param array $data
     * @return string
     */
    protected function _multiCreate(array $data)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->_loadProductById($this->getRequest()->getParam('product_id'));
        $websiteIds = $product->getWebsiteIds();
        foreach ($data as $singleData) {
            try {
                if (!is_array($singleData)) {
                    $this->_errorMessage(self::RESOURCE_DATA_INVALID, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
                }
                /* @var $validator Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website */
                $validator = Mage::getModel('Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website');
                if (!$validator->isValidDataForWebsiteAssignmentToProduct($product, $singleData)) {
                    foreach ($validator->getErrors() as $error) {
                        $this->_errorMessage($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST, array(
                            'website_id' => isset($singleData['website_id']) ? $singleData['website_id'] : null,
                            'product_id' => $product->getId(),
                        ));
                    }
                    $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
                }

                /* @var $website Mage_Core_Model_Website */
                $website = Mage::getModel('Mage_Core_Model_Website')->load($singleData['website_id']);
                $websiteIds[] = $website->getId(); // Existence of a website is checked in the validator
                $product->setWebsiteIds($websiteIds);

                $product->save();

                /**
                 * Do copying data to stores
                 */
                if (isset($singleData['copy_to_stores'])) {
                    foreach ($singleData['copy_to_stores'] as $storeData) {
                        Mage::getModel('Mage_Catalog_Model_Product')
                            ->setStoreId($storeData['store_from'])
                            ->load($product->getId())
                            ->setStoreId($storeData['store_to'])
                            ->save();
                    }
                }

                $this->_successMessage(
                    Mage_Api2_Model_Resource::RESOURCE_UPDATED_SUCCESSFUL,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array(
                        'website_id' => $website->getId(),
                        'product_id' => $product->getId(),
                    )
                );
            } catch (Mage_Api2_Exception $e) {
                // pre-validation errors are already added
                if ($e->getMessage() != self::RESOURCE_DATA_PRE_VALIDATION_ERROR) {
                    $this->_errorMessage(
                        $e->getMessage(),
                        $e->getCode(),
                        array(
                            'website_id' => isset($singleData['website_id']) ? $singleData['website_id'] : null,
                            'product_id' => $product->getId(),
                        )
                    );
                }
            } catch (Exception $e) {
                $this->_errorMessage(
                    Mage_Api2_Model_Resource::RESOURCE_INTERNAL_ERROR,
                    Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR,
                    array(
                        'website_id' => isset($singleData['website_id']) ? $singleData['website_id'] : null,
                        'product_id' => $product->getId(),
                    )
                );
            }
        }
    }

    /**
     * Product websites update is not available
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

    /**
     * Product website unassign
     */
    protected function _delete()
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->_loadProductById($this->getRequest()->getParam('product_id'));

        /* @var $website Mage_Core_Model_Website */
        $website = $this->_loadWebsiteById($this->getRequest()->getParam('website_id'));

        /* @var $validator Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website */
        $validator = Mage::getModel('Mage_Catalog_Model_Api2_Product_Website_Validator_Admin_Website');
        if (!$validator->isWebsiteAssignedToProduct($website, $product)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $websiteIds = $product->getWebsiteIds();
        // Existence of a key is checked in the validator
        unset($websiteIds[array_search($website->getId(), $websiteIds)]);
        $product->setWebsiteIds($websiteIds);

        try {
            $product->save();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Get resource location
     *
     * @param Mage_Core_Model_Website $website
     * @return string URL
     */
    protected function _getLocation($website)
    {
        /* @var $apiTypeRoute Mage_Api2_Model_Route_ApiType */
        $apiTypeRoute = Mage::getModel('Mage_Api2_Model_Route_ApiType');

        $chain = $apiTypeRoute->chain(
            new Zend_Controller_Router_Route($this->getConfig()->getRouteWithEntityTypeAction($this->getResourceType()))
        );
        $params = array(
            'api_type' => $this->getRequest()->getApiType(),
            'product_id' => $this->getRequest()->getParam('product_id'),
            'website_id' => $website->getId()
        );
        $uri = $chain->assemble($params);

        return '/' . $uri;
    }
}
