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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review service
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Review_Service_Product
{
    protected $_product;

    protected $_storageManager;

    protected $_objectManager;

    protected $_review;

    public function __construct(Magento_ObjectManager $objectManager, Mage_Review_Model_Review $review,
                                Mage_Core_Model_StoreManager $storeManager)
    {
        $this->_objectManager = $objectManager;
        $this->_review = $review;
        $this->_storageManager = $storeManager;
    }

    public function getDictionary($productId)
    {
        $this->_product = $this->_objectManager->create('Mage_Catalog_Model_Product');
        $this->_product->load($productId);
        $this->_review->getEntitySummary($this->_product, $this->_storageManager->getStore()->getId());

        $dictionary = array(
            'ratingSummary' => $this->_product->getRatingSummary()
                ->getRatingSummary(),
            'reviewsCount'  => $this->_product->getRatingSummary()->getReviewsCount(),
            'reviewsUrl'     => Mage::getUrl('review/product/list',
                array(
                    'id'        => $this->_product->getId(),
                    'category'  => $this->_product->getCategoryId()
                ))
        );
        return $dictionary;
    }
}