<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

interface FactoryInterface
{
    /**
     * Return search client
     *
     * @param array $options
     * @return ClientInterface
     */
    public function create(array $options = []);
}
