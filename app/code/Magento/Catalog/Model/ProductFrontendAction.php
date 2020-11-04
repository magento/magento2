<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductFrontendActionInterface;

/**
 * @inheritdoc
 */
class ProductFrontendAction extends AbstractModel implements ProductFrontendActionInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\ProductFrontendAction::class);
    }

    /**
     * @inheritdoc
     */
    public function getActionId()
    {
        return $this->getData('action_id');
    }

    /**
     * @inheritdoc
     */
    public function setActionId($actionId)
    {
        $this->setData('action_id', $actionId);
    }

    /**
     * @inheritdoc
     */
    public function getVisitorId()
    {
        return $this->getData('visitor_id');
    }

    /**
     * @inheritdoc
     */
    public function setVisitorId($visitorId)
    {
        $this->setData('visitor_id', $visitorId);
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData('customer_id');
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        $this->setData('product_id', $productId);
    }

    /**
     * @inheritdoc
     */
    public function getAddedAt()
    {
        return $this->getData('added_at');
    }

    /**
     * @inheritdoc
     */
    public function setAddedAt($addedAt)
    {
        $this->setData('added_at');
    }

    /**
     * @inheritdoc
     */
    public function getTypeId()
    {
        return $this->getData('type_id');
    }

    /**
     * @inheritdoc
     */
    public function setTypeId($typeId)
    {
        $this->setData('type_id', $typeId);
    }
}
