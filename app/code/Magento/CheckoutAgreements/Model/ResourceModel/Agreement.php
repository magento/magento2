<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model\ResourceModel;

/**
 * Resource Model for Checkout Agreement
 */
class Agreement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param string $connectionName
     * @codeCoverageIgnore
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

        $this->getConnection()->delete(
            $this->getTable('checkout_agreement_used_in_forms'),
            ['agreement_id = ?' => $object->getId()]
        );

        foreach ((array)$object->getData('used_in_forms') as $form) {
            $formArray = [
                'agreement_id' => $object->getId(),
                'used_in_forms' => $form
            ];
            $this->getConnection()->insert($this->getTable('checkout_agreement_used_in_forms'), $formArray);
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
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('checkout_agreement_store'), ['store_id'])
            ->where('agreement_id = :agreement_id');

        $stores = $this->getConnection()->fetchCol($select, [':agreement_id' => $object->getId()]);

        if ($stores) {
            $object->setData('stores', $stores);
        }

        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('checkout_agreement_used_in_forms'), ['used_in_forms'])
            ->where('agreement_id = :agreement_id');

        $forms = $this->getConnection()->fetchCol($select, [':agreement_id' => $object->getId()]);

        if ($forms) {
            $object->setData('used_in_forms', $forms);
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
