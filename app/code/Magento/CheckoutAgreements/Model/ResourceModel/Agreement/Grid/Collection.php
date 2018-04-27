<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Grid;

/**
 * CheckoutAgreement Grid Collection
 */
class Collection extends \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection
{

    /**
     * {@inheritdoc}
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        parent::load($printQuery, $logQuery);

        $this->addStoresToResult();

        return $this;
    }

    /**
     * @return void
     */
    private function addStoresToResult()
    {
        $stores = $this->getStoresForAgreements();

        if (!empty($stores)) {
            $storesByAgreementId = [];

            foreach ($stores as $storeData) {
                $storesByAgreementId[$storeData['agreement_id']][] = $storeData['store_id'];
            }

            foreach ($this as $item) {
                $agreementId = $item->getData('agreement_id');

                if (!isset($storesByAgreementId[$agreementId])) {
                    continue;
                }

                $item->setData('stores', $storesByAgreementId[$agreementId]);
            }
        }
    }

    /**
     * @return array
     */
    private function getStoresForAgreements()
    {
        $agreementId = $this->getColumnValues('agreement_id');

        if (!empty($agreementId)) {
            $select = $this->getConnection()->select()->from(
                ['agreement_store' => 'checkout_agreement_store']
            )->where(
                'agreement_store.agreement_id IN (?)',
                $agreementId
            );

            return $this->getConnection()->fetchAll($select);
        }

        return [];
    }
}
