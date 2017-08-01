<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model\ResourceModel;

/**
 * Resource Model for Checkout Agreement
 * @since 2.0.0
 */
class Agreement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     * @since 2.0.0
     */
    protected $filterManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param string $connectionName
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        $connectionName = null
    ) {
        $this->filterManager = $filterManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->getConnection()->delete(
            $this->getTable('checkout_agreement_store'),
            ['agreement_id = ?' => $object->getId()]
        );

        foreach ((array)$object->getData('stores') as $storeId) {
            $storeArray = [
                'agreement_id' => $object->getId(),
                'store_id' => $storeId
            ];
            $this->getConnection()->insert($this->getTable('checkout_agreement_store'), $storeArray);
        }

        return parent::_afterSave($object);
    }

    /**
     * Method to run after load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @since 2.0.0
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('checkout_agreement_store'), ['store_id'])
            ->where('agreement_id = :agreement_id');

        $stores = $this->getConnection()->fetchCol($select, [':agreement_id' => $object->getId()]);

        if ($stores) {
            $object->setData('stores', $stores);
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
     * @since 2.0.0
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $select->join(
                ['cps' => $this->getTable('checkout_agreement_store')],
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
