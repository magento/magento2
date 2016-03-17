<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Client;

interface ClientFactoryInterface
{
    /**
     * Return search client
     *
     * @param array $options
     * @return ClientInterface
     */
    public function create(array $options = []);
}
