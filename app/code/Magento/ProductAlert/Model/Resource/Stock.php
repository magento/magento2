<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\Resource;

/**
 * Product alert for back in stock resource model
 */
class Stock extends \Magento\ProductAlert\Model\Resource\AbstractResource
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory)
    {
        $this->_dateFactory = $dateFactory;
        parent::__construct($resource);
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
