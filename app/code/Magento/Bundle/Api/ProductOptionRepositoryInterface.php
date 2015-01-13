<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

interface ProductOptionRepositoryInterface
{
    /**
     * Get option for bundle product
     *
     * @param string $productSku
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     */
    public function get($productSku, $optionId);

    /**
     * Get all options for bundle product
     *
     * @param string $productSku
     * @return \Magento\Bundle\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     */
    public function getList($productSku);

    /**
     * Remove bundle option
     *
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function delete(\Magento\Bundle\Api\Data\OptionInterface $option);

    /**
     * Remove bundle option
     *
     * @param string $productSku
     * @param int $optionId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function deleteById($productSku, $optionId);

    /**
     * Add new option for bundle product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Webapi\Exception
     */
    public function save(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Bundle\Api\Data\OptionInterface $option
    );
}
