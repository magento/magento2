<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

/**
 * @api
 * @since 2.0.0
 */
interface RepositoryInterface
{
    /**
     * Get attributes
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     * @since 2.0.0
     */
    public function getList();
}
