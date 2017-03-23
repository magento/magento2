<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ProductAlert\Model\ResourceModel;

/**
 * Product alert for back in stock resource model
 */
class Stock extends \Magento\ProductAlert\Model\ResourceModel\AbstractResource
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        $connectionName = null
    ) {
        $this->_dateFactory = $dateFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_alert_stock', 'alert_stock_id');
    }

    /**
     * Before save action
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (is_null($object->getId()) && $object->getCustomerId() && $object->getProductId() && $object->getWebsiteId()
        ) {
            if ($row = $this->_getAlertRow($object)) {
                $object->addData($row);
                $object->setStatus(0);
            }
        }
        if (is_null($object->getAddDate())) {
            $object->setAddDate($this->_dateFactory->create()->gmtDate());
            $object->setStatus(0);
        }
        return parent::_beforeSave($object);
    }
}
