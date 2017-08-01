<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

/**
 * Option manager for bundle products
 *
 * @api
 * @since 2.0.0
 */
interface ProductOptionManagementInterface
{
    /**
     * Add new option for bundle product
     *
     * @param \Magento\Bundle\Api\Data\OptionInterface $option
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
     */
    public function save(\Magento\Bundle\Api\Data\OptionInterface $option);
}
