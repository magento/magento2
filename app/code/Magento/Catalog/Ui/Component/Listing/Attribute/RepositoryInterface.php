<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

/**
 * @api
 */
interface RepositoryInterface
{
    /**
     * Get attributes
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getList();
}
