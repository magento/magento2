<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model;

interface AdapterFactoryInterface
{
    /**
     * Return search adapter
     *
     * @return \Magento\Elasticsearch\Model\Adapter\Elasticsearch
     */
    public function createAdapter();
}
