<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

interface FactoryInterface
{
    /**
     * Return search client
     *
     * @param array $options
     * @return mixed
     */
    public function create(array $options = []);
}
