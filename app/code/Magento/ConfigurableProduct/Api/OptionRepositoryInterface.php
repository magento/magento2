<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

interface OptionRepositoryInterface
{
    /**
     * Get option for configurable product
     *
     * @param string $productSku
     * @param int $optionId
     * @return \Magento\ConfigurableProduct\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     */
    public function get($productSku, $optionId);

    /**
     * Get all options for configurable product
     *
     * @param string $productSku
     * @return \Magento\ConfigurableProduct\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     */
    public function getList($productSku);

    /**
     * Remove option from configurable product
     *
     * @param Data\OptionInterface $option
     * @return bool
     */
    public function delete(\Magento\ConfigurableProduct\Api\Data\OptionInterface $option);

    /**
     * Remove option from configurable product
     *
     * @param string $productSku
     * @param int $optionId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     */
    public function deleteById($productSku, $optionId);

    /**
     * Save option
     *
     * @param string $productSku
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \InvalidArgumentException
     */
    public function save($productSku, \Magento\ConfigurableProduct\Api\Data\OptionInterface $option);
}
