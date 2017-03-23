<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * Interface ProductAttributeInterface
 * @api
 */
interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\ProductAttributeInterface
{
    const CODE_IS_DOWNLOADABLE = 'is_downloadable';
}
