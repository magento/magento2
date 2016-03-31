<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Framework\DataObject;

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
