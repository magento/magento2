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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product\Link;

/**
 * Catalog product links collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Product object
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Product Link model class
     *
     * @var \Magento\Catalog\Model\Product\Link
     */
    protected $_linkModel;

    /**
     * Product Link Type identifier
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_linkTypeId;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Product\Link', 'Magento\Catalog\Model\Resource\Product\Link');
    }

    /**
     * Declare link model and initialize type attributes join
     *
     * @param \Magento\Catalog\Model\Product\Link $linkModel
     * @return $this
     */
    public function setLinkModel(\Magento\Catalog\Model\Product\Link $linkModel)
    {
        $this->_linkModel = $linkModel;
        if ($linkModel->hasLinkTypeId()) {
            $this->_linkTypeId = $linkModel->getLinkTypeId();
        }
        return $this;
    }

    /**
     * Retrieve collection link model
     *
     * @return \Magento\Catalog\Model\Product\Link
     */
    public function getLinkModel()
    {
        return $this->_linkModel;
    }

    /**
     * Initialize collection parent product and add limitation join
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Retrieve collection base product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Add link's type to filter
     *
     * @return $this
     */
    public function addLinkTypeIdFilter()
    {
        if ($this->_linkTypeId) {
            $this->addFieldToFilter('link_type_id', array('eq' => $this->_linkTypeId));
        }
        return $this;
    }

    /**
     * Add product to filter
     *
     * @return $this
     */
    public function addProductIdFilter()
    {
        if ($this->getProduct() && $this->getProduct()->getId()) {
            $this->addFieldToFilter('product_id', array('eq' => $this->getProduct()->getId()));
        }
        return $this;
    }

    /**
     * Join attributes
     *
     * @return $this
     */
    public function joinAttributes()
    {
        if (!$this->getLinkModel()) {
            return $this;
        }
        $attributes = $this->getLinkModel()->getAttributes();
        $adapter = $this->getConnection();
        foreach ($attributes as $attribute) {
            $table = $this->getLinkModel()->getAttributeTypeTable($attribute['type']);
            $alias = sprintf('link_attribute_%s_%s', $attribute['code'], $attribute['type']);

            $aliasInCondition = $adapter->quoteColumnAs($alias, null);
            $this->getSelect()->joinLeft(
                array($alias => $table),
                $aliasInCondition .
                '.link_id = main_table.link_id AND ' .
                $aliasInCondition .
                '.product_link_attribute_id = ' .
                (int)$attribute['id'],
                array($attribute['code'] => 'value')
            );
        }

        return $this;
    }
}
