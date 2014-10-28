<?php
/**
 * Catalog Configurable Product Attribute Model
 *
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
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Framework\Model\Context;

/**
 * @method Attribute _getResource()
 * @method Attribute getResource()
 * @method int getProductId()
 * @method Attribute setProductId(int $value)
 * @method int getAttributeId()
 * @method Attribute setAttributeId(int $value)
 * @method int getPosition()
 * @method Attribute setPosition(int $value)
 * @method Attribute setProductAttribute(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $value)
 * @method \Magento\Eav\Model\Entity\Attribute\AbstractAttribute getProductAttribute()
 */
class Attribute extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute');
    }

    /**
     * Add price data to attribute
     *
     * @param array $priceData
     * @return $this
     */
    public function addPrice($priceData)
    {
        $data = $this->getPrices();
        if (is_null($data)) {
            $data = array();
        }
        $data[] = $priceData;
        $this->setPrices($data);
        return $this;
    }

    /**
     * Retrieve attribute label
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->getData('use_default') && $this->getProductAttribute()) {
            return $this->getProductAttribute()->getStoreLabel();
        } else if (is_null($this->getData('label')) && $this->getProductAttribute()) {
            $this->setData('label', $this->getProductAttribute()->getStoreLabel());
        }

        return $this->getData('label');
    }

    /**
     * After save process
     *
     * @return $this
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        $this->_getResource()->saveLabel($this);
        $this->_getResource()->savePrices($this);
        return $this;
    }

    /**
     * Load counfigurable attribute by product and product's attribute
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute  $attribute
     * @return void
     */
    public function loadByProductAndAttribute($product, $attribute)
    {
        $id = $this->_getResource()->getIdByProductIdAndAttributeId($this, $product->getId(), $attribute->getId());
        if ($id) {
            $this->load($id);
        }
    }

    /**
     * Delete configurable attributes by product id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteByProduct($product)
    {
        $this->_getResource()->deleteAttributesByProductId($product->getId());
    }
}
