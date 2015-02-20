<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Resource\Quote;

interface CollectionFactoryInterface
{
    public function create(array $data = array());
}
