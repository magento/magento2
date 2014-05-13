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
namespace Magento\Paypal\Model\Resource;

/**
 * PayPal resource model for certificate based authentication
 */
class Cert extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->_coreDate = $coreDate;
        $this->dateTime = $dateTime;
        parent::__construct($resource);
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
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
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
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(array('main_table' => $this->getMainTable()));

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

        $data = $adapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }
        return $object;
    }
}
