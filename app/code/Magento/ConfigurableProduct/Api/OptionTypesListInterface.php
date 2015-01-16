<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

interface OptionTypesListInterface
{
    /**
     * Get all available option types for configurable product
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Webapi\Exception
     */
    public function getItems();
}
