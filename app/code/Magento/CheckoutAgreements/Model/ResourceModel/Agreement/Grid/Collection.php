<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Grid;

/**
 * CheckoutAgreement Grid Collection
 */
class Collection extends \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection
{

    /**
<<<<<<< HEAD
     * {@inheritdoc}
=======
     * @inheritdoc
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======
     * Add stores to result
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======
     * Get stores for agreements
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return array
     */
    private function getStoresForAgreements()
    {
        $agreementId = $this->getColumnValues('agreement_id');

        if (!empty($agreementId)) {
            $select = $this->getConnection()->select()->from(
<<<<<<< HEAD
                ['agreement_store' => 'checkout_agreement_store']
=======
                ['agreement_store' => $this->getResource()->getTable('checkout_agreement_store')]
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            )->where(
                'agreement_store.agreement_id IN (?)',
                $agreementId
            );

            return $this->getConnection()->fetchAll($select);
        }

        return [];
    }
}
