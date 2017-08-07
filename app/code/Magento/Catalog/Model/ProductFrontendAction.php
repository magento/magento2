<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductFrontendActionInterface;

/**
 * @inheritdoc
 * @since 2.2.0
 */
class ProductFrontendAction extends AbstractModel implements ProductFrontendActionInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.2.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\ProductFrontendAction::class);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getActionId()
    {
        return $this->getData('action_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setActionId($actionId)
    {
        $this->setData('action_id', $actionId);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getVisitorId()
    {
        return $this->getData('visitor_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setVisitorId($visitorId)
    {
        $this->setData('visitor_id', $visitorId);
    }

    /**
     * @return mixed
     * @since 2.2.0
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setCustomerId($customerId)
    {
        $this->setData('customer_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setProductId($productId)
    {
        $this->setData('product_id', $productId);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getAddedAt()
    {
        return $this->getData('added_at');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setAddedAt($addedAt)
    {
        $this->setData('added_at');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getTypeId()
    {
        return $this->getData('type_id');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setTypeId($typeId)
    {
        $this->setData('type_id', $typeId);
    }
}
