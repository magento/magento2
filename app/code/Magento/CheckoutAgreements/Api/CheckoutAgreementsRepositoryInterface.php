<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Api;

/**
 * Interface CheckoutAgreementsRepositoryInterface
 * @api
 * @since 2.0.0
 */
interface CheckoutAgreementsRepositoryInterface
{
    /**
     * Return data object for specified checkout agreement ID and store.
     *
     * @param int $id
     * @param int $storeId
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface
     * @since 2.0.0
     */
    public function get($id, $storeId = null);

    /**
     * Lists active checkout agreements.
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface[]
     * @since 2.0.0
     */
    public function getList();

    /**
     * Create/Update new checkout agreements with data object values
     *
     * @param \Magento\CheckoutAgreements\Api\Data\AgreementInterface $data
     * @param int $storeId
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a ID is sent but the entity does not exist
     * @since 2.0.0
     */
    public function save(\Magento\CheckoutAgreements\Api\Data\AgreementInterface $data, $storeId = null);

    /**
     * Delete checkout agreement
     *
     * @param \Magento\CheckoutAgreements\Api\Data\AgreementInterface $data
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException If there is a problem with the input
     * @since 2.0.0
     */
    public function delete(\Magento\CheckoutAgreements\Api\Data\AgreementInterface $data);

    /**
     * Delete checkout agreement by id
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a ID is sent but the entity does not exist
     * @throws \Magento\Framework\Exception\CouldNotDeleteException If there is a problem with the input
     * @since 2.0.0
     */
    public function deleteById($id);
}
