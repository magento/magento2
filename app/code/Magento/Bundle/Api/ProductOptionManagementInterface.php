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
     */
    public function save(\Magento\Bundle\Api\Data\OptionInterface $option);
}
