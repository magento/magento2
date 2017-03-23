<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Quote;

interface CollectionFactoryInterface
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Reports\Model\ResourceModel\Quote\Collection
     */
    public function create(array $data = []);
}
