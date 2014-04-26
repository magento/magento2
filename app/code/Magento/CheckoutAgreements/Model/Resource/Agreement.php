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
namespace Magento\CheckoutAgreements\Model\Resource;

/**
 * Resource Model for Checkout Agreement
 */
class Agreement extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Filter\FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
        parent::__construct($resource);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('checkout_agreement', 'agreement_id');
    }

    /**
     * Method to run before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        // format height
        $height = $object->getContentHeight();
        $height = $this->filterManager->stripTags($height);
        if (!$height) {
            $height = '';
        }
        if ($height && preg_match('/[0-9]$/', $height)) {
            $height .= 'px';
        }
        $object->setContentHeight($height);
        return parent::_beforeSave($object);
    }

    /**
     * Method to run after save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = array('agreement_id = ?' => $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('checkout_agreement_store'), $condition);

        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['agreement_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('checkout_agreement_store'), $storeArray);
        }

        return parent::_afterSave($object);
    }

    /**
     * Method to run after load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('checkout_agreement_store'),
            array('store_id')
        )->where(
            'agreement_id = :agreement_id'
        );

        if ($stores = $this->_getReadAdapter()->fetchCol($select, array(':agreement_id' => $object->getId()))) {
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get load select
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $select->join(
                array('cps' => $this->getTable('checkout_agreement_store')),
                $this->getMainTable() . '.agreement_id = cps.agreement_id'
            )->where(
                'is_active=1'
            )->where(
                'cps.store_id IN (0, ?)',
                $object->getStoreId()
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }
        return $select;
    }
}
