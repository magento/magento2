<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Shopingcart Products Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Shopcart\Product;

class Collection extends \Magento\Reports\Model\Resource\Product\Collection
{
    /**
     * Join fields
     *
     * @return $this
     */
    protected function _joinFields()
    {
        parent::_joinFields();
        $this->addAttributeToSelect('price')->addCartsCount()->addOrdersCount();

        return $this;
    }

    /**
     * Set date range
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        $this->getSelect()->reset();
        return $this;
    }
}
