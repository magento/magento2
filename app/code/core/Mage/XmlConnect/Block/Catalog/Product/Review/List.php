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
 * Review block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Review_List extends Mage_XmlConnect_Block_Catalog_Product_Review
{
    /**
     * Store reviews collection
     *
     * @var Mage_Review_Model_Resource_Review_Collection
     */
    protected $_reviewCollection = null;

    /**
     * Produce reviews list xml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getReviewsXmlObject()
    {
        $reviewsXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<reviews></reviews>'));
        $collection    = $this->_getReviewCollection();

        if (!$collection) {
            return $reviewsXmlObj;
        }
        foreach ($collection->getItems() as $review) {
            $reviewXmlObj = $this->reviewToXmlObject($review);
            if ($reviewXmlObj) {
                $reviewsXmlObj->appendChild($reviewXmlObj);
            }
        }

        return $reviewsXmlObj;
    }

    /**
     * Retrieve reviews collection with all prepared data and limitations
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getReviewCollection()
    {
        if (is_null($this->_reviewCollection)) {
            $product = $this->getProduct();
            $request = $this->getRequest();
            if (!$product) {
                return null;
            }
            /** @var $collection Mage_Review_Model_Resource_Review_Collection */
            $collection = Mage::getResourceModel('Mage_Review_Model_Resource_Review_Collection')
                ->addEntityFilter('product', $product->getId())->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter('approved')->setDateOrder();

            /**
             * Apply offset and count
             */
            $offset = (int)$request->getParam('offset', 0);
            $count  = (int)$request->getParam('count', 0);
            $count  = $count <= 0 ? 1 : $count;
            $collection->getSelect()->limit($count, $offset);

            $this->_reviewCollection = $collection;
        }
        return $this->_reviewCollection;
    }

    /**
     * Render reviews list xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product')->load((int)$this->getRequest()->getParam('id', 0));
        if ($product->getId()) {
            $this->setProduct($product);
        }

        return $this->getReviewsXmlObject()->asNiceXml();
    }
}
