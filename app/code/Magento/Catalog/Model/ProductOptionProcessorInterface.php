<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Framework\DataObject;

/**
 * Allows to dump and apply product configurations
 *
 * @api
 * @since 100.0.2
 */
interface ProductOptionProcessorInterface
{
    /**
     * Convert product option data to buy request data
     *
     * @param ProductOptionInterface $productOption
     * @return DataObject
     */
    public function convertToBuyRequest(ProductOptionInterface $productOption);

    /**
     * Convert buy request data to product option data
     *
     * @param DataObject $request
     * @return array
     */
    public function convertToProductOption(DataObject $request);
}
