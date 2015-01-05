<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
