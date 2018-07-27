<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

/**
 * Manage options of configurable product
 *
 * @api
 */
interface OptionRepositoryInterface
{
    /**
     * Get option for configurable product
     *
     * @param string $sku
     * @param int $id
     * @return \Magento\ConfigurableProduct\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($sku, $id);

    /**
     * Get all options for configurable product
     *
     * @param string $sku
     * @return \Magento\ConfigurableProduct\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getList($sku);

    /**
     * Remove option from configurable product
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface $option
     * @return bool
     */
    public function delete(\Magento\ConfigurableProduct\Api\Data\OptionInterface $option);

    /**
     * Remove option from configurable product
     *
     * @param string $sku
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function deleteById($sku, $id);

    /**
     * Save option
     *
     * @param string $sku
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \InvalidArgumentException
     */
    public function save($sku, \Magento\ConfigurableProduct\Api\Data\OptionInterface $option);
}
