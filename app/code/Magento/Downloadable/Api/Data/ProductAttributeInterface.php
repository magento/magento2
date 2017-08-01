<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * Interface ProductAttributeInterface
 * @api
 * @since 2.1.0
 */
interface ProductAttributeInterface extends \Magento\Catalog\Api\Data\ProductAttributeInterface
{
    const CODE_IS_DOWNLOADABLE = 'is_downloadable';
}
