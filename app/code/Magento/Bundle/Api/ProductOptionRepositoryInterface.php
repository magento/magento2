<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

/**
 * Interface ProductOptionRepositoryInterface
 * @api
 */
interface ProductOptionRepositoryInterface
{
    /**
     * Get option for bundle product
     *
     * @param string $sku
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($sku, $optionId);

    /**
     * Get all options for bundle product
     *
     * @param string $sku
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getList($sku);

    /**
     * Remove bundle option
     *
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function delete(\Magento\Bundle\Api\Data\OptionInterface $option);

    /**
     * Remove bundle option
     *
     * @param string $sku
     * @param int $optionId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function deleteById($sku, $optionId);

    /**
     * Add new option for bundle product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    );
}
