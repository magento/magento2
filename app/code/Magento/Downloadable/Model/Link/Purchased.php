<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

/**
 * Downloadable links purchased model
 *
 * @method \Magento\Downloadable\Model\ResourceModel\Link\Purchased _getResource()
 * @method \Magento\Downloadable\Model\ResourceModel\Link\Purchased getResource()
 * @method int getOrderId()
 * @method \Magento\Downloadable\Model\Link\Purchased setOrderId(int $value)
 * @method string getOrderIncrementId()
 * @method \Magento\Downloadable\Model\Link\Purchased setOrderIncrementId(string $value)
 * @method int getOrderItemId()
 * @method \Magento\Downloadable\Model\Link\Purchased setOrderItemId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Downloadable\Model\Link\Purchased setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Downloadable\Model\Link\Purchased setUpdatedAt(string $value)
 * @method int getCustomerId()
 * @method \Magento\Downloadable\Model\Link\Purchased setCustomerId(int $value)
 * @method string getProductName()
 * @method \Magento\Downloadable\Model\Link\Purchased setProductName(string $value)
 * @method string getSku()
 * @method \Magento\Downloadable\Model\Link\Purchased setSku(string $value)
 * @method string getLinkSectionTitle()
 * @method \Magento\Downloadable\Model\Link\Purchased setLinkSectionTitle(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Purchased extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Enter description here...
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Downloadable\Model\ResourceModel\Link\Purchased');
        parent::_construct();
    }

    /**
     * Check order id
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (null == $this->getOrderId()) {
            throw new \Exception(__('Order id cannot be null'));
        }
        return parent::beforeSave();
    }
}
