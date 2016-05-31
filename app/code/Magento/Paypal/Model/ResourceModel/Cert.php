<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\ResourceModel;

/**
 * PayPal resource model for certificate based authentication
 */
class Cert extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->_coreDate = $coreDate;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paypal_cert', 'cert_id');
    }

    /**
     * Set date of last update
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->dateTime->formatDate($this->_coreDate->gmtDate()));
        return parent::_beforeSave($object);
    }

    /**
     * Load model by website id
     *
     * @param \Magento\Paypal\Model\Cert $object
     * @param bool $strictLoad
     * @return \Magento\Paypal\Model\Cert
     */
    public function loadByWebsite($object, $strictLoad = true)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(['main_table' => $this->getMainTable()]);

        if ($strictLoad) {
            $select->where('main_table.website_id =?', $object->getWebsiteId());
        } else {
            $select->where(
                'main_table.website_id IN(0, ?)',
                $object->getWebsiteId()
            )->order(
                'main_table.website_id DESC'
            )->limit(
                1
            );
        }

        $data = $connection->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }
        return $object;
    }
}
