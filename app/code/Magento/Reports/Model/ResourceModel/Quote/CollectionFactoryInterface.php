<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Quote;

/**
 * @api
 * @since 2.0.0
 */
interface CollectionFactoryInterface
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Reports\Model\ResourceModel\Quote\Collection
     * @since 2.0.0
     */
    public function create(array $data = []);
}
