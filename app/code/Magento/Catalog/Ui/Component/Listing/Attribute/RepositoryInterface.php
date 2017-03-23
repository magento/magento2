<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

interface RepositoryInterface
{
    /**
     * Get attributes
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    public function getList();
}
